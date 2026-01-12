<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecordManualPaymentRequest;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Subscription;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use App\Services\SubscriptionStatusService;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function create(Member $member)
    {
        $activePlans = MembershipPlan::active()->get();

        return Inertia::render('Subscriptions/Create', [
            'member' => $member,
            'plans' => $activePlans,
        ]);
    }

    public function store(StoreSubscriptionRequest $request)
    {
        $subscription = DB::transaction(function () use ($request) {
            $data = $request->validated();

            $plan = MembershipPlan::findOrFail($data['membership_plan_id']);

            $startDate = $data['start_date'];
            $endDate = now()->parse($startDate)->addDays($plan->duration_in_days);

            // Create subscription with pending_payment status
            $subscription = Subscription::create([
                'member_id' => $data['member_id'],
                'membership_plan_id' => $data['membership_plan_id'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'pending_payment',  // Pay-first: start as pending
                'auto_renew' => $data['auto_renew'] ?? false,
            ]);

            // Auto-generate invoice
            $invoiceService = app(InvoiceService::class);
            $invoiceService->generateInvoiceForSubscription($subscription);

            return $subscription;
        });

        // Redirect to payment selection page
        return redirect()->route('subscriptions.payment', $subscription->id)
            ->with('success', 'Subscription created. Please complete payment to activate.');
    }

    public function renew(Subscription $subscription, InvoiceService $invoiceService)
    {
        if ($subscription->status !== 'active') {
            return redirect()->back()
                ->with('error', 'Cannot renew inactive subscription');
        }

        $newSubscription = DB::transaction(function () use ($subscription, $invoiceService) {
            $plan = $subscription->membershipPlan;
            $newStartDate = $subscription->end_date->copy()->addDay();
            $newEndDate = $newStartDate->copy()->addDays($plan->duration_in_days);

            // Create new subscription with pending_payment status (pay-first)
            $newSubscription = Subscription::create([
                'member_id' => $subscription->member_id,
                'membership_plan_id' => $subscription->membership_plan_id,
                'start_date' => $newStartDate,
                'end_date' => $newEndDate,
                'status' => 'pending_payment',
                'auto_renew' => $subscription->auto_renew,
                'metadata' => [
                    'renewal_of' => $subscription->id,
                    'created_at' => now()->toIso8601String(),
                ],
            ]);

            // Generate invoice for the renewal
            $invoiceService->generateInvoiceForSubscription($newSubscription);

            return $newSubscription;
        });

        // Redirect to payment page
        return redirect()->route('subscriptions.payment', $newSubscription->id)
            ->with('success', 'Renewal subscription created. Payment link will be sent to member.');
    }

    public function renewManual(Subscription $subscription)
    {
        if ($subscription->status !== 'active') {
            return redirect()->back()
                ->with('error', 'Cannot renew inactive subscription');
        }

        $subscription->load(['member', 'membershipPlan']);

        $plan = $subscription->membershipPlan;
        $newStartDate = $subscription->end_date->copy()->addDay();
        $newEndDate = $newStartDate->copy()->addDays($plan->duration_in_days);

        return Inertia::render('Subscriptions/RenewManual', [
            'subscription' => $subscription,
            'newStartDate' => $newStartDate->format('Y-m-d'),
            'newEndDate' => $newEndDate->format('Y-m-d'),
            'amount' => $plan->price,
            'currency' => $plan->currency,
        ]);
    }

    public function processManualRenewal(
        Subscription $subscription,
        RecordManualPaymentRequest $request,
        InvoiceService $invoiceService,
        PaymentService $paymentService,
        SubscriptionStatusService $subscriptionStatusService
    ) {
        if ($subscription->status !== 'active') {
            return redirect()->back()
                ->with('error', 'Cannot renew inactive subscription');
        }

        $data = $request->validated();

        $newSubscription = DB::transaction(function () use (
            $subscription,
            $data,
            $invoiceService,
            $paymentService

        ) {
            $plan = $subscription->membershipPlan;
            $newStartDate = $subscription->end_date->copy()->addDay();
            $newEndDate = $newStartDate->copy()->addDays($plan->duration_in_days);

            // Create new subscription with active status
            $newSubscription = Subscription::create([
                'member_id' => $subscription->member_id,
                'membership_plan_id' => $subscription->membership_plan_id,
                'start_date' => $newStartDate,
                'end_date' => $newEndDate,
                'status' => 'active',
                'auto_renew' => $subscription->auto_renew,
                'metadata' => [
                    'renewal_of' => $subscription->id,
                    'payment_type' => 'manual',
                    'created_at' => now()->toIso8601String(),
                ],
            ]);

            // Generate invoice
            $invoice = $invoiceService->generateInvoiceForSubscription($newSubscription);

            // Record manual payment
            $payment = $paymentService->recordManualPayment([
                'member_id' => $subscription->member_id,
                'subscription_id' => $newSubscription->id,
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'GHS',
                'payment_method' => $data['payment_method'],
                'transaction_id' => $data['transaction_id'] ?? 'MANUAL-'.strtoupper(uniqid()),
                'metadata' => [
                    'notes' => $data['notes'] ?? null,
                    'recorded_by' => auth()->id(),
                    'renewal' => true,
                    'previous_subscription_id' => $subscription->id,
                ],
            ]);

            // Link payment to invoice
            $invoiceService->linkPaymentToInvoice($payment, $invoice);

            // Mark old subscription as expired
            $subscription->update(['status' => 'expired']);

            return $newSubscription;
        });

        return redirect()->route('members.show', $subscription->member_id)
            ->with('success', 'Subscription renewed successfully with manual payment');
    }

    public function cancel(Subscription $subscription)
    {
        if ($subscription->status !== 'active') {
            return redirect()->back()
                ->with('error', 'Subscription is already cancelled or expired');
        }

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'auto_renew' => false,
        ]);

        return redirect()->route('members.show', $subscription->member_id)
            ->with('success', 'Subscription cancelled successfully');
    }
}
