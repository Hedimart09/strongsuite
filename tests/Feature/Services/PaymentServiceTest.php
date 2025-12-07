<?php

use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\PaymentGateway\FlutterwaveGateway;
use App\Services\PaymentGateway\PaystackGateway;
use App\Services\PaymentGateway\StripeGateway;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.paystack.secret_key' => 'test_paystack_secret',
        'services.paystack.public_key' => 'test_paystack_public',
        'services.paystack.merchant_email' => 'test@example.com',
        'services.flutterwave.secret_key' => 'test_flutterwave_secret',
        'services.flutterwave.public_key' => 'test_flutterwave_public',
        'services.flutterwave.secret_hash' => 'test_flutterwave_hash',
        'services.flutterwave.encryption_key' => 'test_flutterwave_encryption',
        'services.stripe.secret' => 'test_stripe_secret',
        'services.stripe.key' => 'test_stripe_key',
        'services.stripe.webhook_secret' => 'test_stripe_webhook',
    ]);

    $this->paymentService = new PaymentService;
    $this->member = Member::factory()->create();
    $this->plan = MembershipPlan::factory()->create();
    $this->subscription = Subscription::factory()->create([
        'member_id' => $this->member->id,
        'membership_plan_id' => $this->plan->id,
    ]);
});

it('can retrieve available gateways', function () {
    $gateways = $this->paymentService->getAvailableGateways();

    expect($gateways)->toBeArray()
        ->toHaveCount(3)
        ->toContain('paystack', 'flutterwave', 'stripe');
});

it('can get a specific gateway', function () {
    $paystack = $this->paymentService->getGateway('paystack');
    $flutterwave = $this->paymentService->getGateway('flutterwave');
    $stripe = $this->paymentService->getGateway('stripe');

    expect($paystack)->toBeInstanceOf(PaystackGateway::class)
        ->and($flutterwave)->toBeInstanceOf(FlutterwaveGateway::class)
        ->and($stripe)->toBeInstanceOf(StripeGateway::class);
});

it('returns null for invalid gateway', function () {
    $gateway = $this->paymentService->getGateway('invalid');

    expect($gateway)->toBeNull();
});

it('can record manual payment', function () {
    Log::shouldReceive('info')->once();

    $paymentData = [
        'member_id' => $this->member->id,
        'subscription_id' => $this->subscription->id,
        'amount' => 10000,
        'currency' => 'GHS',
        'payment_method' => 'cash',
        'transaction_id' => 'MANUAL-'.uniqid(),
        'metadata' => ['note' => 'Cash payment at reception'],
    ];

    $payment = $this->paymentService->recordManualPayment($paymentData);

    expect($payment)->toBeInstanceOf(Payment::class)
        ->and($payment->member_id)->toBe($this->member->id)
        ->and($payment->subscription_id)->toBe($this->subscription->id)
        ->and($payment->amount)->toBe(10000)
        ->and($payment->currency)->toBe('GHS')
        ->and($payment->payment_method)->toBe('cash')
        ->and($payment->payment_gateway)->toBeNull()
        ->and($payment->status)->toBe('completed')
        ->and($payment->payment_date)->not->toBeNull();

    $this->assertDatabaseHas('payments', [
        'member_id' => $this->member->id,
        'amount' => 10000,
        'payment_method' => 'cash',
        'status' => 'completed',
    ]);
});

it('can record manual payment with minimal data', function () {
    Log::shouldReceive('info')->once();

    $paymentData = [
        'member_id' => $this->member->id,
        'amount' => 5000,
        'payment_method' => 'mobile_money',
    ];

    $payment = $this->paymentService->recordManualPayment($paymentData);

    expect($payment)->toBeInstanceOf(Payment::class)
        ->and($payment->currency)->toBe('GHS')
        ->and($payment->subscription_id)->toBeNull()
        ->and($payment->status)->toBe('completed');
});

it('returns error for invalid gateway on initialization', function () {
    $result = $this->paymentService->initializePayment('invalid-gateway', []);

    expect($result)->toBeArray()
        ->and($result['status'])->toBe('error')
        ->and($result['message'])->toContain('not found');
});

it('can initialize payment with paystack', function () {
    Http::fake([
        'api.paystack.co/transaction/initialize' => Http::response([
            'status' => true,
            'message' => 'Authorization URL created',
            'data' => [
                'authorization_url' => 'https://checkout.paystack.com/test123',
                'access_code' => 'test123',
                'reference' => 'TEST_REF_123',
            ],
        ], 200),
    ]);

    $paymentData = [
        'member_id' => $this->member->id,
        'subscription_id' => $this->subscription->id,
        'amount' => 10000,
        'currency' => 'GHS',
        'email' => $this->member->email,
        'metadata' => ['subscription_id' => $this->subscription->id],
    ];

    $result = $this->paymentService->initializePayment('paystack', $paymentData);

    expect($result)->toBeArray()
        ->and($result['status'])->toBe('success')
        ->and($result['reference'])->toBe('TEST_REF_123')
        ->and($result['authorization_url'])->toContain('checkout.paystack.com');

    $this->assertDatabaseHas('payments', [
        'member_id' => $this->member->id,
        'transaction_id' => 'TEST_REF_123',
        'status' => 'pending',
        'payment_gateway' => 'paystack',
    ]);
});

it('returns error when gateway initialization fails', function () {
    Http::fake([
        'api.paystack.co/transaction/initialize' => Http::response([
            'status' => false,
            'message' => 'Invalid email address',
        ], 400),
    ]);

    $paymentData = [
        'member_id' => $this->member->id,
        'amount' => 10000,
        'email' => 'invalid-email',
    ];

    $result = $this->paymentService->initializePayment('paystack', $paymentData);

    expect($result)->toBeArray()
        ->and($result['status'])->toBe('error');
});

it('returns error for invalid gateway on verification', function () {
    $result = $this->paymentService->verifyAndRecordPayment('invalid-gateway', 'REF123');

    expect($result)->toBeArray()
        ->and($result['status'])->toBe('error')
        ->and($result['message'])->toContain('not found');
});

it('can verify and record successful payment', function () {
    $payment = Payment::factory()->create([
        'member_id' => $this->member->id,
        'transaction_id' => 'TEST_REF_123',
        'status' => 'pending',
        'payment_date' => null,
    ]);

    Http::fake([
        'api.paystack.co/transaction/verify/TEST_REF_123' => Http::response([
            'status' => true,
            'message' => 'Verification successful',
            'data' => [
                'reference' => 'TEST_REF_123',
                'amount' => 1000000,
                'currency' => 'GHS',
                'status' => 'success',
                'paid_at' => now()->toIso8601String(),
            ],
        ], 200),
    ]);

    $result = $this->paymentService->verifyAndRecordPayment('paystack', 'TEST_REF_123');

    expect($result)->toBeArray()
        ->and($result['status'])->toBe('success')
        ->and($result['payment'])->toBeInstanceOf(Payment::class)
        ->and($result['message'])->toContain('verified and recorded');

    $payment->refresh();

    expect($payment->status)->toBe('completed')
        ->and($payment->payment_date)->not->toBeNull();
});

it('returns error when payment record not found during verification', function () {
    Http::fake([
        'api.paystack.co/transaction/verify/NONEXISTENT' => Http::response([
            'status' => true,
            'message' => 'Verification successful',
            'data' => [
                'reference' => 'NONEXISTENT',
                'amount' => 1000000,
                'currency' => 'GHS',
                'status' => 'success',
                'paid_at' => now()->toIso8601String(),
            ],
        ], 200),
    ]);

    $result = $this->paymentService->verifyAndRecordPayment('paystack', 'NONEXISTENT');

    expect($result)->toBeArray()
        ->and($result['status'])->toBe('error')
        ->and($result['message'])->toContain('Payment record not found');
});

it('returns error for invalid gateway on webhook', function () {
    $result = $this->paymentService->processWebhook('invalid-gateway', []);

    expect($result)->toBeArray()
        ->and($result['status'])->toBe('error')
        ->and($result['message'])->toContain('not found');
});

it('can process successful webhook', function () {
    Log::shouldReceive('info')->once();

    $payment = Payment::factory()->create([
        'member_id' => $this->member->id,
        'transaction_id' => 'TEST_REF_WEBHOOK',
        'status' => 'pending',
        'payment_date' => null,
    ]);

    $payload = [
        'event' => 'charge.success',
        'data' => [
            'reference' => 'TEST_REF_WEBHOOK',
            'amount' => 1000000,
            'currency' => 'GHS',
            'status' => 'success',
            'customer' => [
                'email' => $this->member->email,
            ],
            'paid_at' => now()->toIso8601String(),
        ],
    ];

    $jsonPayload = json_encode($payload);
    $signature = hash_hmac('sha512', $jsonPayload, config('services.paystack.secret_key'));

    $request = \Illuminate\Http\Request::create(
        '/webhook/paystack',
        'POST',
        [],
        [],
        [],
        ['HTTP_X-PAYSTACK-SIGNATURE' => $signature],
        $jsonPayload
    );

    app()->instance('request', $request);

    $result = $this->paymentService->processWebhook('paystack', $payload);

    expect($result)->toBeArray()
        ->and($result['status'])->toBe('success')
        ->and($result['payment'])->toBeInstanceOf(Payment::class)
        ->and($result['message'])->toContain('processed successfully');

    $payment->refresh();

    expect($payment->status)->toBe('completed')
        ->and($payment->payment_date)->not->toBeNull()
        ->and($payment->metadata)->toHaveKey('webhook_processed_at');
});

it('returns error when payment record not found during webhook processing', function () {
    $payload = [
        'event' => 'charge.success',
        'data' => [
            'reference' => 'NONEXISTENT_WEBHOOK',
            'amount' => 1000000,
            'currency' => 'GHS',
            'status' => 'success',
            'customer' => [
                'email' => 'test@example.com',
            ],
            'paid_at' => now()->toIso8601String(),
        ],
    ];

    $jsonPayload = json_encode($payload);
    $signature = hash_hmac('sha512', $jsonPayload, config('services.paystack.secret_key'));

    $request = \Illuminate\Http\Request::create(
        '/webhook/paystack',
        'POST',
        [],
        [],
        [],
        ['HTTP_X-PAYSTACK-SIGNATURE' => $signature],
        $jsonPayload
    );

    app()->instance('request', $request);

    $result = $this->paymentService->processWebhook('paystack', $payload);

    expect($result)->toBeArray()
        ->and($result['status'])->toBe('error')
        ->and($result['message'])->toContain('Payment record not found');
});

it('ignores non-payment webhook events', function () {
    $payload = [
        'event' => 'customer.created',
        'data' => [
            'customer_code' => 'CUS_test123',
        ],
    ];

    $jsonPayload = json_encode($payload);
    $signature = hash_hmac('sha512', $jsonPayload, config('services.paystack.secret_key'));

    $request = \Illuminate\Http\Request::create(
        '/webhook/paystack',
        'POST',
        [],
        [],
        [],
        ['HTTP_X-PAYSTACK-SIGNATURE' => $signature],
        $jsonPayload
    );

    app()->instance('request', $request);

    $result = $this->paymentService->processWebhook('paystack', $payload);

    expect($result)->toBeArray()
        ->and($result['status'])->toBe('ignored');
});
