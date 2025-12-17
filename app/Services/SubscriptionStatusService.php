<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;

class SubscriptionStatusService
{
    public function activateSubscription(Subscription $subscription): void
    {
        if ($subscription->status === 'active') {
            return; // Already active, idempotent
        }

        $subscription->update([
            'status' => 'active',
            'metadata' => array_merge($subscription->metadata ?? [], [
                'activated_at' => now()->toIso8601String(),
                'activated_from' => $subscription->status,
            ]),
        ]);

        Log::info('Subscription activated', [
            'subscription_id' => $subscription->id,
            'member_id' => $subscription->member_id,
        ]);
    }

    public function suspendSubscription(Subscription $subscription, string $reason): void
    {
        $subscription->update([
            'status' => 'suspended',
            'metadata' => array_merge($subscription->metadata ?? [], [
                'suspended_at' => now()->toIso8601String(),
                'suspension_reason' => $reason,
            ]),
        ]);

        Log::info('Subscription suspended', [
            'subscription_id' => $subscription->id,
            'reason' => $reason,
        ]);
    }

    public function handlePaymentCompleted(Payment $payment): void
    {
        if (! $payment->subscription_id) {
            return; // Payment not linked to subscription
        }

        $subscription = Subscription::find($payment->subscription_id);

        if (! $subscription) {
            Log::warning('Subscription not found for payment', [
                'payment_id' => $payment->id,
                'subscription_id' => $payment->subscription_id,
            ]);

            return;
        }

        if ($subscription->status === 'pending_payment') {
            $this->activateSubscription($subscription);
        }
    }
}
