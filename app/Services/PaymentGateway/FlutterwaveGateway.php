<?php

namespace App\Services\PaymentGateway;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlutterwaveGateway implements PaymentGatewayInterface
{
    protected string $secretKey;

    protected string $publicKey;

    protected string $baseUrl = 'https://api.flutterwave.com/v3';

    public function __construct()
    {
        $this->secretKey = config('services.flutterwave.secret_key');
        $this->publicKey = config('services.flutterwave.public_key');
    }

    public function initializePayment(array $data): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->secretKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/payments", [
            'tx_ref' => $data['reference'] ?? $this->generateReference(),
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'GHS',
            'redirect_url' => $data['callback_url'] ?? url('/payments/flutterwave/callback'),
            'customer' => [
                'email' => $data['email'],
                'name' => $data['name'] ?? '',
            ],
            'customizations' => [
                'title' => $data['title'] ?? config('app.name'),
                'description' => $data['description'] ?? 'Payment',
            ],
            'meta' => $data['metadata'] ?? [],
        ]);

        if ($response->successful() && $response->json()['status'] === 'success') {
            return [
                'status' => 'success',
                'data' => $response->json()['data'],
                'authorization_url' => $response->json()['data']['link'],
                'reference' => $data['reference'] ?? $this->generateReference(),
            ];
        }

        Log::error('Flutterwave initialization failed', [
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
        ])->get("{$this->baseUrl}/transactions/{$reference}/verify");

        if ($response->successful() && $response->json()['status'] === 'success') {
            $data = $response->json()['data'];

            if ($data['status'] === 'successful') {
                return [
                    'status' => 'success',
                    'data' => $data,
                    'amount' => $data['amount'],
                    'currency' => $data['currency'],
                    'reference' => $data['tx_ref'],
                    'transaction_id' => $data['id'],
                    'paid_at' => $data['created_at'],
                ];
            }
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

        if ($event === 'charge.completed' && $data['status'] === 'successful') {
            return [
                'status' => 'success',
                'event' => 'payment_completed',
                'reference' => $data['tx_ref'],
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'customer_email' => $data['customer']['email'],
                'transaction_id' => $data['id'],
                'paid_at' => $data['created_at'],
            ];
        }

        return [
            'status' => 'ignored',
            'event' => $event,
        ];
    }

    public function getName(): string
    {
        return 'flutterwave';
    }

    protected function generateReference(): string
    {
        return 'FLW_'.strtoupper(uniqid().bin2hex(random_bytes(4)));
    }

    protected function verifyWebhookSignature(): bool
    {
        $signature = request()->header('verif-hash');

        if (! $signature) {
            return false;
        }

        $secretHash = config('services.flutterwave.secret_hash');

        return hash_equals($signature, $secretHash);
    }
}
