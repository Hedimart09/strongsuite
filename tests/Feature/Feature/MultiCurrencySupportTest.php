<?php

use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Money\Money;

uses(RefreshDatabase::class);

it('can create money object from membership plan', function () {
    $plan = MembershipPlan::factory()->create([
        'price' => 50000,
        'currency' => 'GHS',
    ]);

    $money = $plan->getMoney();

    expect($money)->toBeInstanceOf(Money::class)
        ->and($money->getAmount())->toBe('50000')
        ->and($money->getCurrency()->getCode())->toBe('GHS');
});

it('can format membership plan price with money package', function () {
    $plan = MembershipPlan::factory()->create([
        'price' => 50000,
        'currency' => 'GHS',
    ]);

    $formatted = $plan->getFormattedPrice();

    expect($formatted)->toBeString()
        ->and($formatted)->toContain('500');
});

it('supports multiple currencies for membership plans', function () {
    $ghsPlan = MembershipPlan::factory()->create([
        'price' => 50000,
        'currency' => 'GHS',
    ]);

    $usdPlan = MembershipPlan::factory()->create([
        'price' => 10000,
        'currency' => 'USD',
    ]);

    $eurPlan = MembershipPlan::factory()->create([
        'price' => 8000,
        'currency' => 'EUR',
    ]);

    expect($ghsPlan->getMoney()->getCurrency()->getCode())->toBe('GHS')
        ->and($usdPlan->getMoney()->getCurrency()->getCode())->toBe('USD')
        ->and($eurPlan->getMoney()->getCurrency()->getCode())->toBe('EUR');
});

it('can create money object from payment', function () {
    $member = Member::factory()->create();

    $payment = Payment::factory()->create([
        'member_id' => $member->id,
        'amount' => 25000,
        'currency' => 'GHS',
    ]);

    $money = $payment->getMoney();

    expect($money)->toBeInstanceOf(Money::class)
        ->and($money->getAmount())->toBe('25000')
        ->and($money->getCurrency()->getCode())->toBe('GHS');
});

it('can format payment amount with money package', function () {
    $member = Member::factory()->create();

    $payment = Payment::factory()->create([
        'member_id' => $member->id,
        'amount' => 25000,
        'currency' => 'GHS',
    ]);

    $formatted = $payment->getFormattedAmount();

    expect($formatted)->toBeString()
        ->and($formatted)->toContain('250');
});

it('supports multiple currencies for payments', function () {
    $member = Member::factory()->create();

    $ghsPayment = Payment::factory()->create([
        'member_id' => $member->id,
        'amount' => 50000,
        'currency' => 'GHS',
    ]);

    $usdPayment = Payment::factory()->create([
        'member_id' => $member->id,
        'amount' => 10000,
        'currency' => 'USD',
    ]);

    $eurPayment = Payment::factory()->create([
        'member_id' => $member->id,
        'amount' => 8000,
        'currency' => 'EUR',
    ]);

    expect($ghsPayment->getMoney()->getCurrency()->getCode())->toBe('GHS')
        ->and($usdPayment->getMoney()->getCurrency()->getCode())->toBe('USD')
        ->and($eurPayment->getMoney()->getCurrency()->getCode())->toBe('EUR');
});

it('preserves amount precision with money objects', function () {
    $plan = MembershipPlan::factory()->create([
        'price' => 123456,
        'currency' => 'USD',
    ]);

    $member = Member::factory()->create();

    $payment = Payment::factory()->create([
        'member_id' => $member->id,
        'amount' => 987654,
        'currency' => 'EUR',
    ]);

    expect($plan->getMoney()->getAmount())->toBe('123456')
        ->and($payment->getMoney()->getAmount())->toBe('987654');
});

it('handles zero amounts correctly', function () {
    $plan = MembershipPlan::factory()->create([
        'price' => 0,
        'currency' => 'GHS',
    ]);

    $money = $plan->getMoney();

    expect($money->getAmount())->toBe('0')
        ->and($money->isZero())->toBeTrue();
});
