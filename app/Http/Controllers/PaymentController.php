<?php

namespace App\Http\Controllers;

use App\Http\Requests\InitializePaymentRequest;
use App\Http\Requests\RecordManualPaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use App\Services\SubscriptionStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected InvoiceService $invoiceService,
        protected SubscriptionStatusService $subscriptionStatusService
    ) {}

    public function initialize(InitializePaymentRequest $request)
    {
        $data = $request->validated();

        $subscription = Subscription::with('membershipPlan', 'member')
            ->findOrFail($data['subscription_id']);

        // Get or create invoice for this subscription
        $invoice = $subscription->invoices()->where('status', '!=', 'paid')->first();

        if (! $invoice) {
            $invoice = $this->invoiceService->generateInvoiceForSubscription($subscription);
        }

        // Prepare payment data
        $paymentData = [
            'member_id' => $subscription->member_id,
            'subscription_id' => $subscription->id,
            'amount' => $data['amount'] ?? $invoice->total_amount,
            'currency' => $data['currency'] ?? $invoice->currency,
            'email' => $subscription->member->email,
            'callback_url' => $data['callback_url'] ?? route('payments.verify', $data['gateway']),
            'metadata' => [
                'subscription_id' => $subscription->id,
                'invoice_id' => $invoice->id,
                'member_id' => $subscription->member_id,
            ],
        ];

        $result = $this->paymentService->initializePayment($data['gateway'], $paymentData);

        if ($result['status'] === 'success') {
            // Store invoice ID in session for verification
            session([
                'pending_invoice_id' => $invoice->id,
                'pending_subscription_id' => $subscription->id,
            ]);

            return response()->json([
                'status' => 'success',
                'authorization_url' => $result['authorization_url'],
                'reference' => $result['reference'],
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => $result['message'] ?? 'Payment initialization failed',
        ], 400);
    }

    public function verify(string $gateway, Request $request)
    {
        $reference = $request->query('reference') ?? $request->query('trxref') ?? $request->query('transaction_id');

        if (! $reference) {
            return redirect()->route('dashboard')
                ->with('error', 'Payment reference not found');
        }

        $result = $this->paymentService->verifyAndRecordPayment($gateway, $reference);

        if ($result['status'] === 'success') {
            DB::transaction(function () use ($result) {
                $payment = $result['payment'];

                // Link payment to invoice if we have one in session
                if ($invoiceId = session('pending_invoice_id')) {
                    $invoice = Invoice::find($invoiceId);
                    if ($invoice) {
                        $this->invoiceService->linkPaymentToInvoice($payment, $invoice);
                    }
                }

                // Activate subscription if payment is for a subscription
                if ($payment->subscription_id) {
                    $this->subscriptionStatusService->handlePaymentCompleted($payment);
                }
            });

            // Clear session data
            session()->forget(['pending_invoice_id', 'pending_subscription_id']);

            return redirect()->route('payments.success', $result['payment']->id)
                ->with('success', 'Payment completed successfully!');
        }

        return redirect()->route('payments.failed')
            ->with('error', $result['message'] ?? 'Payment verification failed');
    }

    public function webhook(string $gateway, Request $request)
    {
        Log::info('Webhook received', [
            'gateway' => $gateway,
            'payload' => $request->all(),
        ]);

        $result = $this->paymentService->processWebhook($gateway, $request->all());

        if ($result['status'] === 'success') {
            DB::transaction(function () use ($result) {
                $payment = $result['payment'];

                // Link to invoice if available
                if ($payment->subscription_id) {
                    $invoice = Invoice::where('subscription_id', $payment->subscription_id)
                        ->where('status', '!=', 'paid')
                        ->first();

                    if ($invoice) {
                        $this->invoiceService->linkPaymentToInvoice($payment, $invoice);
                    }
                }

                // Activate subscription
                $this->subscriptionStatusService->handlePaymentCompleted($payment);
            });

            return response()->json(['status' => 'success'], 200);
        }

        return response()->json([
            'status' => $result['status'],
            'message' => $result['message'] ?? 'Webhook processing failed',
        ], $result['status'] === 'ignored' ? 200 : 400);
    }

    public function recordManual(RecordManualPaymentRequest $request)
    {
        $data = $request->validated();

        $payment = DB::transaction(function () use ($data) {
            // Record the manual payment
            $payment = $this->paymentService->recordManualPayment([
                'member_id' => $data['member_id'],
                'subscription_id' => $data['subscription_id'] ?? null,
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'GHS',
                'payment_method' => $data['payment_method'],
                'transaction_id' => $data['transaction_id'] ?? 'MANUAL-'.strtoupper(uniqid()),
                'metadata' => [
                    'notes' => $data['notes'] ?? null,
                    'recorded_by' => auth()->id(),
                ],
            ]);

            // Link to invoice if provided
            if (isset($data['invoice_id'])) {
                $invoice = Invoice::find($data['invoice_id']);
                if ($invoice) {
                    $this->invoiceService->linkPaymentToInvoice($payment, $invoice);
                }
            } elseif (isset($data['subscription_id'])) {
                // Find or create invoice for subscription
                $subscription = Subscription::find($data['subscription_id']);
                if ($subscription) {
                    $invoice = $subscription->invoices()->where('status', '!=', 'paid')->first();
                    if (! $invoice) {
                        $invoice = $this->invoiceService->generateInvoiceForSubscription($subscription);
                    }
                    $this->invoiceService->linkPaymentToInvoice($payment, $invoice);
                }
            }

            // Activate subscription if applicable
            $this->subscriptionStatusService->handlePaymentCompleted($payment);

            return $payment;
        });

        return redirect()->back()
            ->with('success', 'Payment recorded successfully');
    }

    public function index(Request $request)
    {
        $query = Payment::query()
            ->with(['member', 'subscription.membershipPlan', 'invoice'])
            ->latest('payment_date');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('payment_date', [
                $request->input('start_date'),
                $request->input('end_date'),
            ]);
        }

        // Filter by member
        if ($request->filled('member_id')) {
            $query->where('member_id', $request->input('member_id'));
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                    ->orWhereHas('member', function ($memberQuery) use ($search) {
                        $memberQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('member_id', 'like', "%{$search}%");
                    });
            });
        }

        $payments = $query->paginate(20);

        return Inertia::render('Payments/Index', [
            'payments' => $payments,
            'filters' => $request->only(['status', 'payment_method', 'start_date', 'end_date', 'member_id', 'search']),
        ]);
    }

    public function show(Payment $payment)
    {
        $payment->load(['member', 'subscription.membershipPlan', 'invoice']);

        return Inertia::render('Payments/Show', [
            'payment' => $payment,
        ]);
    }

    public function success(Payment $payment)
    {
        $payment->load(['member', 'subscription.membershipPlan', 'invoice']);

        return Inertia::render('Payments/PublicSuccess', [
            'payment' => $payment,
        ]);
    }

    public function failed()
    {
        return Inertia::render('Payments/PublicFailed', [
            'error' => session('error'),
        ]);
    }
}
