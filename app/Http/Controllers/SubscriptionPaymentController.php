<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecordManualPaymentRequest;
use App\Mail\PaymentLinkMail;
use App\Models\Subscription;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use App\Services\SubscriptionStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class SubscriptionPaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected InvoiceService $invoiceService,
        protected SubscriptionStatusService $subscriptionStatusService
    ) {}

    public function show(Subscription $subscription)
    {
        $subscription->load(['member', 'membershipPlan', 'invoices']);

        // Get or create invoice for this subscription
        $invoice = $subscription->invoices()->where('status', '!=', 'paid')->first();

        if (! $invoice) {
            $invoice = $this->invoiceService->generateInvoiceForSubscription($subscription);
        }

        $availableGateways = $this->paymentService->getAvailableGateways();

        return Inertia::render('Subscriptions/Payment', [
            'subscription' => $subscription,
            'invoice' => $invoice,
            'available_gateways' => $availableGateways,
        ]);
    }

    public function processOnline(Subscription $subscription, Request $request)
    {
        $request->validate([
            'gateway' => ['required', 'in:paystack,flutterwave,stripe'],
        ]);

        $subscription->load('membershipPlan', 'member');

        $invoice = $subscription->invoices()->where('status', '!=', 'paid')->first();

        if (! $invoice) {
            $invoice = $this->invoiceService->generateInvoiceForSubscription($subscription);
        }

        $paymentData = [
            'member_id' => $subscription->member_id,
            'subscription_id' => $subscription->id,
            'amount' => $invoice->total_amount,
            'currency' => $invoice->currency,
            'email' => $subscription->member->email,
            'callback_url' => route('payments.verify', $request->input('gateway')),
            'metadata' => [
                'subscription_id' => $subscription->id,
                'invoice_id' => $invoice->id,
                'member_id' => $subscription->member_id,
            ],
        ];

        $result = $this->paymentService->initializePayment($request->input('gateway'), $paymentData);

        if ($result['status'] === 'success') {
            session([
                'pending_invoice_id' => $invoice->id,
                'pending_subscription_id' => $subscription->id,
            ]);

            // Send payment link to member's email
            Mail::to($subscription->member->email)->send(
                new PaymentLinkMail(
                    member: $subscription->member,
                    subscription: $subscription,
                    paymentLink: $result['authorization_url'],
                    amount: $invoice->total_amount,
                    currency: $invoice->currency
                )
            );

            return redirect()->back()
                ->with('success', 'Payment link has been sent to '.$subscription->member->email);
        }

        return redirect()->back()
            ->with('error', $result['message'] ?? 'Payment initialization failed');
    }

    public function showManualPaymentForm(Subscription $subscription)
    {
        $subscription->load(['member', 'membershipPlan', 'invoices']);

        // Get or create invoice for this subscription
        $invoice = $subscription->invoices()->where('status', '!=', 'paid')->first();

        if (! $invoice) {
            $invoice = $this->invoiceService->generateInvoiceForSubscription($subscription);
        }

        return Inertia::render('Subscriptions/ManualPayment', [
            'subscription' => $subscription,
            'invoice' => $invoice,
        ]);
    }

    public function processManual(Subscription $subscription, RecordManualPaymentRequest $request)
    {
        // Merge subscription data into validated request data
        $data = array_merge($request->validated(), [
            'member_id' => $subscription->member_id,
            'subscription_id' => $subscription->id,
        ]);

        $payment = DB::transaction(function () use ($subscription, $data) {
            $subscription->load('membershipPlan');

            // Get or create invoice
            $invoice = $subscription->invoices()->where('status', '!=', 'paid')->first();
            if (! $invoice) {
                $invoice = $this->invoiceService->generateInvoiceForSubscription($subscription);
            }

            // Record manual payment
            $payment = $this->paymentService->recordManualPayment([
                'member_id' => $data['member_id'],
                'subscription_id' => $data['subscription_id'],
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'GHS',
                'payment_method' => $data['payment_method'],
                'transaction_id' => $data['transaction_id'] ?? 'MANUAL-'.strtoupper(uniqid()),
                'metadata' => [
                    'notes' => $data['notes'] ?? null,
                    'recorded_by' => auth()->id(),
                ],
            ]);

            // Link to invoice
            $this->invoiceService->linkPaymentToInvoice($payment, $invoice);

            // Activate subscription
            $this->subscriptionStatusService->handlePaymentCompleted($payment);

            return $payment;
        });

        return redirect()->route('members.show', $subscription->member_id)
            ->with('success', 'Payment recorded and subscription activated successfully');
    }
}
