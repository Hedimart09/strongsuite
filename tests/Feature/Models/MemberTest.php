<?php

use App\Models\Attendance;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\Subscription;

test('can create a member', function () {
    $member = Member::factory()->create();

    expect($member)->toBeInstanceOf(Member::class)
        ->and($member->member_id)->not->toBeNull()
        ->and($member->qr_code)->not->toBeNull()
        ->and($member->status)->toBe('active');
});

test('member has unique member_id', function () {
    $member1 = Member::factory()->create();
    $member2 = Member::factory()->create();

    expect($member1->member_id)->not->toBe($member2->member_id);
});

test('member has unique qr_code', function () {
    $member1 = Member::factory()->create();
    $member2 = Member::factory()->create();

    expect($member1->qr_code)->not->toBe($member2->qr_code);
});

test('member can have subscriptions', function () {
    $member = Member::factory()->create();
    $plan = MembershipPlan::factory()->create();

    $subscription = Subscription::factory()
        ->for($member)
        ->for($plan, 'membershipPlan')
        ->create();

    expect($member->subscriptions)->toHaveCount(1)
        ->and($member->subscriptions->first()->id)->toBe($subscription->id);
});

test('member can have payments', function () {
    $member = Member::factory()->create();

    Payment::factory()
        ->for($member)
        ->count(3)
        ->create();

    expect($member->payments)->toHaveCount(3);
});

test('member can have attendance records', function () {
    $member = Member::factory()->create();

    Attendance::factory()
        ->for($member)
        ->count(5)
        ->create();

    expect($member->attendances)->toHaveCount(5);
});

test('member isActive method works correctly', function () {
    $activeMember = Member::factory()->active()->create();
    $inactiveMember = Member::factory()->inactive()->create();

    expect($activeMember->isActive())->toBeTrue()
        ->and($inactiveMember->isActive())->toBeFalse();
});

test('member active subscription relationship works', function () {
    $member = Member::factory()->create();
    $plan = MembershipPlan::factory()->create();

    Subscription::factory()
        ->for($member)
        ->for($plan, 'membershipPlan')
        ->active()
        ->create();

    expect($member->activeSubscription()->count())->toBe(1);
});

test('membership plan can have subscriptions', function () {
    $plan = MembershipPlan::factory()->create();
    $member = Member::factory()->create();

    Subscription::factory()
        ->for($member)
        ->for($plan, 'membershipPlan')
        ->create();

    expect($plan->subscriptions)->toHaveCount(1);
});

test('subscription belongs to member and membership plan', function () {
    $member = Member::factory()->create();
    $plan = MembershipPlan::factory()->create();

    $subscription = Subscription::factory()
        ->for($member)
        ->for($plan, 'membershipPlan')
        ->create();

    expect($subscription->member->id)->toBe($member->id)
        ->and($subscription->membershipPlan->id)->toBe($plan->id);
});

test('subscription isActive method works correctly', function () {
    $member = Member::factory()->create();
    $plan = MembershipPlan::factory()->create();

    $activeSubscription = Subscription::factory()
        ->for($member)
        ->for($plan, 'membershipPlan')
        ->active()
        ->create();

    $expiredSubscription = Subscription::factory()
        ->for($member)
        ->for($plan, 'membershipPlan')
        ->expired()
        ->create();

    expect($activeSubscription->isActive())->toBeTrue()
        ->and($expiredSubscription->isActive())->toBeFalse();
});

test('payment belongs to member and subscription', function () {
    $member = Member::factory()->create();
    $plan = MembershipPlan::factory()->create();

    $subscription = Subscription::factory()
        ->for($member)
        ->for($plan, 'membershipPlan')
        ->create();

    $payment = Payment::factory()
        ->for($member)
        ->for($subscription)
        ->create();

    expect($payment->member->id)->toBe($member->id)
        ->and($payment->subscription->id)->toBe($subscription->id);
});

test('payment isCompleted method works correctly', function () {
    $member = Member::factory()->create();

    $completedPayment = Payment::factory()
        ->for($member)
        ->completed()
        ->create();

    $pendingPayment = Payment::factory()
        ->for($member)
        ->pending()
        ->create();

    expect($completedPayment->isCompleted())->toBeTrue()
        ->and($pendingPayment->isCompleted())->toBeFalse();
});

test('attendance belongs to member', function () {
    $member = Member::factory()->create();

    $attendance = Attendance::factory()
        ->for($member)
        ->create();

    expect($attendance->member->id)->toBe($member->id);
});

test('attendance duration calculation works', function () {
    $member = Member::factory()->create();

    $attendance = Attendance::factory()
        ->for($member)
        ->checkedOut()
        ->create();

    expect($attendance->duration)->toBeGreaterThan(0);
});

test('membership plan formatted price works', function () {
    $plan = MembershipPlan::factory()->create([
        'price' => 40000,
    ]);

    expect($plan->getFormattedPriceAttribute())->toBe('400.00');
});

test('payment formatted amount works', function () {
    $member = Member::factory()->create();

    $payment = Payment::factory()
        ->for($member)
        ->create([
            'amount' => 40000,
        ]);

    expect($payment->getFormattedAmountAttribute())->toBe('400.00');
});
