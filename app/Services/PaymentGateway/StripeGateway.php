<?php

namespace App\Services\PaymentGateway;

use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeGateway implements PaymentGatewayInterface
{
    protected string $secretKey;

    protected string $publicKey;

    protected string $webhookSecret;

    public function __construct()
    {
        $this->secretKey = config('services.stripe.secret');
        $this->publicKey = config('services.stripe.key');
        $this->webhookSecret = config('services.stripe.webhook_secret');

        Stripe::setApiKey($this->secretKey);
    }

    public function initializePayment(array $data): array
    {
        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($data['currency'] ?? 'usd'),
                        'product_data' => [
                            'name' => $data['description'] ?? 'Payment',
                        ],
                        'unit_amount' => $data['amount'] * 100, // Convert to cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $data['success_url'] ?? url('/payments/stripe/success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $data['cancel_url'] ?? url('/payments/stripe/cancel'),
                'customer_email' => $data['email'],
                'metadata' => $data['metadata'] ?? [],
            ]);

            return [
                'status' => 'success',
                'data' => $session,
                'authorization_url' => $session->url,
                'session_id' => $session->id,
                'reference' => $session->id,
            ];
        } catch (ApiErrorException $e) {
            Log::error('Stripe initialization failed', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function verifyPayment(string $reference): array
    {
        try {
            $session = Session::retrieve($reference);

            if ($session->payment_status === 'paid') {
                $paymentIntent = PaymentIntent::retrieve($session->payment_intent);

                return [
                    'status' => 'success',
                    'data' => $session,
                    'amount' => $session->amount_total / 100, // Convert from cents
                    'currency' => strtoupper($session->currency),
                    'reference' => $session->id,
                    'payment_intent_id' => $session->payment_intent,
                    'paid_at' => date('Y-m-d H:i:s', $paymentIntent->created),
                ];
            }

            return [
                'status' => 'failed',
                'message' => 'Payment not completed',
            ];
        } catch (ApiErrorException $e) {
            Log::error('Stripe verification failed', [
                'error' => $e->getMessage(),
                'reference' => $reference,
            ]);

            return [
                'status' => 'failed',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function processWebhook(array $payload): array
    {
        try {
            $event = Webhook::constructEvent(
                request()->getContent(),
                request()->header('Stripe-Signature'),
                $this->webhookSecret
            );
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Invalid webhook signature',
            ];
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            if ($session->payment_status === 'paid') {
                return [
                    'status' => 'success',
                    'event' => 'payment_completed',
                    'reference' => $session->id,
                    'amount' => $session->amount_total / 100,
                    'currency' => strtoupper($session->currency),
                    'customer_email' => $session->customer_email,
                    'payment_intent_id' => $session->payment_intent,
                ];
            }
        }

        return [
            'status' => 'ignored',
            'event' => $event->type,
        ];
    }

    public function getName(): string
    {
        return 'stripe';
    }
}
