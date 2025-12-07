<?php

namespace App\Services\PaymentGateway;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackGateway implements PaymentGatewayInterface
{
    protected string $secretKey;

    protected string $publicKey;

    protected string $baseUrl = 'https://api.paystack.co';

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret_key');
        $this->publicKey = config('services.paystack.public_key');
    }

    public function initializePayment(array $data): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->secretKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/transaction/initialize", [
            'email' => $data['email'],
            'amount' => $data['amount'] * 100, // Convert to kobo/pesewas
            'currency' => $data['currency'] ?? 'GHS',
            'reference' => $data['reference'] ?? $this->generateReference(),
            'callback_url' => $data['callback_url'] ?? null,
            'metadata' => $data['metadata'] ?? [],
        ]);

        if ($response->successful()) {
            return [
                'status' => 'success',
                'data' => $response->json()['data'],
                'authorization_url' => $response->json()['data']['authorization_url'],
                'access_code' => $response->json()['data']['access_code'],
                'reference' => $response->json()['data']['reference'],
            ];
        }

        Log::error('Paystack initialization failed', [
            'response' => $response->json(),
            'status' => $response->status(),
        ]);

        return [
            'status' => 'error',
            'message' => $response->json()['message'] ?? 'Payment initialization failed',
        ];
    }

    public function verifyPayment(string $reference): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->secretKey,
        ])->get("{$this->baseUrl}/transaction/verify/{$reference}");

        if ($response->successful() && $response->json()['data']['status'] === 'success') {
            return [
                'status' => 'success',
                'data' => $response->json()['data'],
                'amount' => $response->json()['data']['amount'] / 100, // Convert from kobo/pesewas
                'currency' => $response->json()['data']['currency'],
                'reference' => $response->json()['data']['reference'],
                'paid_at' => $response->json()['data']['paid_at'],
            ];
        }

        return [
            'status' => 'failed',
            'message' => $response->json()['message'] ?? 'Payment verification failed',
        ];
    }

    public function processWebhook(array $payload): array
    {
        if (! $this->verifyWebhookSignature()) {
            return [
                'status' => 'error',
                'message' => 'Invalid webhook signature',
            ];
        }

        $event = $payload['event'] ?? null;
        $data = $payload['data'] ?? [];

        if ($event === 'charge.success') {
            return [
                'status' => 'success',
                'event' => 'payment_completed',
                'reference' => $data['reference'],
                'amount' => $data['amount'] / 100,
                'currency' => $data['currency'],
                'customer_email' => $data['customer']['email'],
                'paid_at' => $data['paid_at'],
            ];
        }

        return [
            'status' => 'ignored',
            'event' => $event,
        ];
    }

    public function getName(): string
    {
        return 'paystack';
    }

    protected function generateReference(): string
    {
        return 'PSK_'.strtoupper(uniqid().bin2hex(random_bytes(4)));
    }

    protected function verifyWebhookSignature(): bool
    {
        $signature = request()->header('X-Paystack-Signature');

        if (! $signature) {
            return false;
        }

        $computedSignature = hash_hmac('sha512', request()->getContent(), $this->secretKey);

        return hash_equals($signature, $computedSignature);
    }
}
