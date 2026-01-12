<?php

namespace App\Services;

use App\Models\Payment;
use App\Services\PaymentGateway\FlutterwaveGateway;
use App\Services\PaymentGateway\PaymentGatewayInterface;
use App\Services\PaymentGateway\PaystackGateway;
use App\Services\PaymentGateway\StripeGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected array $gateways = [];

    public function __construct()
    {
        $this->gateways = [
            'paystack' => new PaystackGateway,
            'flutterwave' => new FlutterwaveGateway,
            'stripe' => new StripeGateway,
        ];
    }

    public function getGateway(string $name): ?PaymentGatewayInterface
    {
        return $this->gateways[$name] ?? null;
    }

    public function recordManualPayment(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $payment = Payment::create([
                'member_id' => $data['member_id'],
                'subscription_id' => $data['subscription_id'] ?? null,
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'GHS',
                'payment_method' => $data['payment_method'],
                'payment_gateway' => null,
                'transaction_id' => $data['transaction_id'] ?? null,
                'status' => 'completed',
                'payment_date' => now(),
                'metadata' => $data['metadata'] ?? [],
            ]);

            Log::info('Manual payment recorded', [
                'payment_id' => $payment->id,
                'member_id' => $data['member_id'],
                'amount' => $data['amount'],
                'method' => $data['payment_method'],
            ]);

            return $payment;
        });
    }

    public function initializePayment(string $gateway, array $data): array
    {
        $gatewayInstance = $this->getGateway($gateway);

        if (! $gatewayInstance) {
            return [
                'status' => 'error',
                'message' => "Gateway '{$gateway}' not found",
            ];
        }

        return DB::transaction(function () use ($gatewayInstance, $gateway, $data) {
            // Cancel any existing pending payments for this subscription
            if (isset($data['subscription_id'])) {
                Payment::where('subscription_id', $data['subscription_id'])
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'cancelled',
                        'metadata' => DB::raw("JSON_SET(COALESCE(metadata, '{}'), '$.cancelled_reason', 'New payment initialized', '$.cancelled_at', '".now()->toIso8601String()."')"),
                    ]);

                Log::info('Cancelled pending payments for subscription', [
                    'subscription_id' => $data['subscription_id'],
                ]);
            }

            $result = $gatewayInstance->initializePayment($data);

            if ($result['status'] === 'success') {
                Payment::create([
                    'member_id' => $data['member_id'],
                    'subscription_id' => $data['subscription_id'] ?? null,
                    'amount' => $data['amount'],
                    'currency' => $data['currency'] ?? 'GHS',
                    'payment_method' => $gateway,
                    'payment_gateway' => $gateway,
                    'transaction_id' => $result['reference'],
                    'status' => 'pending',
                    'payment_date' => null,
                    'metadata' => array_merge($data['metadata'] ?? [], [
                        'authorization_url' => $result['authorization_url'] ?? null,
                    ]),
                ]);
            }

            return $result;
        });
    }

    public function verifyAndRecordPayment(string $gateway, string $reference): array
    {
        $gatewayInstance = $this->getGateway($gateway);

        if (! $gatewayInstance) {
            return [
                'status' => 'error',
                'message' => "Gateway '{$gateway}' not found",
            ];
        }

        $result = $gatewayInstance->verifyPayment($reference);

        if ($result['status'] === 'success') {
            return DB::transaction(function () use ($reference, $result) {
                $payment = Payment::where('transaction_id', $reference)->first();

                if (! $payment) {
                    return [
                        'status' => 'error',
                        'message' => 'Payment record not found',
                    ];
                }

                // Check if payment is already completed
                if ($payment->status === 'completed') {
                    Log::info('Payment already completed', [
                        'payment_id' => $payment->id,
                        'transaction_id' => $reference,
                    ]);

                    return [
                        'status' => 'success',
                        'payment' => $payment,
                        'message' => 'Payment already verified',
                    ];
                }

                // Check if another payment for this subscription was already completed
                if ($payment->subscription_id) {
                    $existingCompletedPayment = Payment::where('subscription_id', $payment->subscription_id)
                        ->where('status', 'completed')
                        ->where('id', '!=', $payment->id)
                        ->first();

                    if ($existingCompletedPayment) {
                        Log::warning('Duplicate payment attempt detected', [
                            'new_payment_id' => $payment->id,
                            'existing_payment_id' => $existingCompletedPayment->id,
                            'subscription_id' => $payment->subscription_id,
                        ]);

                        // Mark this payment as duplicate
                        $payment->update([
                            'status' => 'refunded',
                            'metadata' => array_merge($payment->metadata ?? [], [
                                'duplicate_of' => $existingCompletedPayment->id,
                                'marked_at' => now()->toIso8601String(),
                                'note' => 'Duplicate payment - subscription already paid',
                            ]),
                        ]);

                        return [
                            'status' => 'error',
                            'message' => 'This subscription has already been paid. Please contact support for a refund.',
                        ];
                    }
                }

                $payment->update([
                    'status' => 'completed',
                    'payment_date' => now(),
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'verified_at' => now()->toIso8601String(),
                        'verification_data' => $result['data'] ?? [],
                    ]),
                ]);

                return [
                    'status' => 'success',
                    'payment' => $payment,
                    'message' => 'Payment verified and recorded successfully',
                ];
            });
        }

        return $result;
    }

    public function processWebhook(string $gateway, array $payload): array
    {
        $gatewayInstance = $this->getGateway($gateway);

        if (! $gatewayInstance) {
            return [
                'status' => 'error',
                'message' => "Gateway '{$gateway}' not found",
            ];
        }

        $result = $gatewayInstance->processWebhook($payload);

        if ($result['status'] === 'success' && $result['event'] === 'payment_completed') {
            return DB::transaction(function () use ($result) {
                $payment = Payment::where('transaction_id', $result['reference'])->first();

                if (! $payment) {
                    return [
                        'status' => 'error',
                        'message' => 'Payment record not found',
                    ];
                }

                // Check if payment is already completed (duplicate webhook)
                if ($payment->status === 'completed') {
                    Log::info('Duplicate webhook received for completed payment', [
                        'payment_id' => $payment->id,
                        'reference' => $result['reference'],
                    ]);

                    return [
                        'status' => 'success',
                        'payment' => $payment,
                        'message' => 'Payment already processed',
                    ];
                }

                // Check if another payment for this subscription was already completed
                if ($payment->subscription_id) {
                    $existingCompletedPayment = Payment::where('subscription_id', $payment->subscription_id)
                        ->where('status', 'completed')
                        ->where('id', '!=', $payment->id)
                        ->first();

                    if ($existingCompletedPayment) {
                        Log::warning('Duplicate payment detected via webhook', [
                            'new_payment_id' => $payment->id,
                            'existing_payment_id' => $existingCompletedPayment->id,
                            'subscription_id' => $payment->subscription_id,
                        ]);

                        // Mark as duplicate
                        $payment->update([
                            'status' => 'refunded',
                            'metadata' => array_merge($payment->metadata ?? [], [
                                'duplicate_of' => $existingCompletedPayment->id,
                                'marked_at' => now()->toIso8601String(),
                                'note' => 'Duplicate payment - subscription already paid',
                            ]),
                        ]);

                        return [
                            'status' => 'error',
                            'message' => 'Duplicate payment detected',
                        ];
                    }
                }

                $payment->update([
                    'status' => 'completed',
                    'payment_date' => $result['paid_at'] ?? now(),
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'webhook_processed_at' => now()->toIso8601String(),
                        'webhook_data' => $result,
                    ]),
                ]);

                Log::info('Payment completed via webhook', [
                    'payment_id' => $payment->id,
                    'reference' => $result['reference'],
                    'amount' => $result['amount'],
                ]);

                return [
                    'status' => 'success',
                    'payment' => $payment,
                    'message' => 'Payment processed successfully',
                ];
            });
        }

        return $result;
    }

    public function getAvailableGateways(): array
    {
        return array_keys($this->gateways);
    }
}
