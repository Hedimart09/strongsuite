<?php

use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->admin()->create();
    $this->actingAs($this->user);
});

// Membership Plans Tests
it('can display membership plans index page', function () {
    $response = $this->get('/membership-plans');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('MembershipPlans/Index'));
});

it('can display membership plan create page', function () {
    $response = $this->get('/membership-plans/create');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('MembershipPlans/Create'));
});

it('can create a membership plan', function () {
    $planData = [
        'name' => 'Monthly Membership',
        'description' => 'Standard monthly membership',
        'price' => 50.00,
        'currency' => 'GHS',
        'duration_in_days' => 30,
        'is_active' => true,
        'features' => ['Gym Access', 'Personal Trainer'],
    ];

    $response = $this->post('/membership-plans', $planData);

    $response->assertRedirect('/membership-plans');
    $this->assertDatabaseHas('membership_plans', [
        'name' => 'Monthly Membership',
        'price' => 5000,
        'currency' => 'GHS',
        'duration_in_days' => 30,
        'is_active' => true,
    ]);
});

it('validates required fields when creating membership plan', function () {
    $response = $this->post('/membership-plans', []);

    $response->assertSessionHasErrors([
        'name',
        'price',
        'currency',
        'duration_in_days',
    ]);
});

it('validates unique name when creating membership plan', function () {
    MembershipPlan::factory()->create(['name' => 'Existing Plan']);

    $response = $this->post('/membership-plans', [
        'name' => 'Existing Plan',
        'price' => 50.00,
        'currency' => 'GHS',
        'duration_in_days' => 30,
    ]);

    $response->assertSessionHasErrors(['name']);
});

it('can display membership plan details', function () {
    $plan = MembershipPlan::factory()->create();

    $response = $this->get("/membership-plans/{$plan->id}");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('MembershipPlans/Show')
        ->has('plan'));
});

it('can display membership plan edit page', function () {
    $plan = MembershipPlan::factory()->create();

    $response = $this->get("/membership-plans/{$plan->id}/edit");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('MembershipPlans/Edit')
        ->has('plan'));
});

it('can update membership plan', function () {
    $plan = MembershipPlan::factory()->create([
        'name' => 'Old Plan',
        'price' => 5000,
    ]);

    $updateData = [
        'name' => 'Updated Plan',
        'description' => $plan->description,
        'price' => 75.00,
        'currency' => $plan->currency,
        'duration_in_days' => $plan->duration_in_days,
        'is_active' => true,
    ];

    $response = $this->put("/membership-plans/{$plan->id}", $updateData);

    $response->assertRedirect("/membership-plans/{$plan->id}");
    $this->assertDatabaseHas('membership_plans', [
        'id' => $plan->id,
        'name' => 'Updated Plan',
        'price' => 7500,
    ]);
});

it('can delete membership plan', function () {
    $plan = MembershipPlan::factory()->create();

    $response = $this->delete("/membership-plans/{$plan->id}");

    $response->assertRedirect('/membership-plans');
    $this->assertDatabaseMissing('membership_plans', ['id' => $plan->id]);
});

it('cannot delete membership plan with subscriptions', function () {
    $plan = MembershipPlan::factory()->create();
    $member = Member::factory()->create();
    Subscription::factory()->create([
        'member_id' => $member->id,
        'membership_plan_id' => $plan->id,
    ]);

    $response = $this->delete("/membership-plans/{$plan->id}");

    $response->assertRedirect();
    $this->assertDatabaseHas('membership_plans', ['id' => $plan->id]);
});

it('can search membership plans', function () {
    MembershipPlan::factory()->create(['name' => 'Monthly Membership']);
    MembershipPlan::factory()->create(['name' => 'Annual Membership']);

    $response = $this->get('/membership-plans?search=Monthly');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('MembershipPlans/Index')
        ->where('plans.data.0.name', 'Monthly Membership'));
});

it('can filter membership plans by status', function () {
    MembershipPlan::factory()->active()->create(['name' => 'Active Plan']);
    MembershipPlan::factory()->inactive()->create(['name' => 'Inactive Plan']);

    $response = $this->get('/membership-plans?status=active');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('MembershipPlans/Index')
        ->where('plans.data.0.is_active', true));
});

// Subscription Tests
it('can display subscription create page', function () {
    $member = Member::factory()->create();

    $response = $this->get("/members/{$member->id}/subscriptions/create");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Subscriptions/Create')
        ->has('member')
        ->has('plans'));
});

it('can create a subscription', function () {
    $member = Member::factory()->create();
    $plan = MembershipPlan::factory()->create(['duration_in_days' => 30]);

    $subscriptionData = [
        'member_id' => $member->id,
        'membership_plan_id' => $plan->id,
        'start_date' => now()->toDateString(),
        'auto_renew' => false,
    ];

    $response = $this->post('/subscriptions', $subscriptionData);

    // Should redirect to payment page with pending_payment status (pay-first flow)
    $subscription = Subscription::where('member_id', $member->id)->first();
    $response->assertRedirect("/subscriptions/{$subscription->id}/payment");
    $this->assertDatabaseHas('subscriptions', [
        'member_id' => $member->id,
        'membership_plan_id' => $plan->id,
        'status' => 'pending_payment',
    ]);
});

it('calculates end date correctly when creating subscription', function () {
    $member = Member::factory()->create();
    $plan = MembershipPlan::factory()->create(['duration_in_days' => 30]);
    $startDate = now()->toDateString();

    $subscriptionData = [
        'member_id' => $member->id,
        'membership_plan_id' => $plan->id,
        'start_date' => $startDate,
    ];

    $this->post('/subscriptions', $subscriptionData);

    $subscription = Subscription::where('member_id', $member->id)->first();
    $expectedEndDate = now()->addDays(30)->toDateString();

    expect($subscription->end_date->toDateString())->toBe($expectedEndDate);
});

it('validates required fields when creating subscription', function () {
    $response = $this->post('/subscriptions', []);

    $response->assertSessionHasErrors([
        'member_id',
        'membership_plan_id',
        'start_date',
    ]);
});

it('can renew a subscription', function () {
    $member = Member::factory()->create();
    $plan = MembershipPlan::factory()->create(['duration_in_days' => 30]);
    $subscription = Subscription::factory()->create([
        'member_id' => $member->id,
        'membership_plan_id' => $plan->id,
        'status' => 'active',
        'start_date' => now(),
        'end_date' => now()->addDays(30),
    ]);

    $response = $this->post("/subscriptions/{$subscription->id}/renew");

    $newSubscription = Subscription::where('member_id', $member->id)
        ->where('id', '!=', $subscription->id)
        ->first();

    expect($newSubscription)->not->toBeNull();

    // Should redirect to payment page (pay-first approach)
    $response->assertRedirect("/subscriptions/{$newSubscription->id}/payment");

    // New subscription should be pending payment
    $expectedStartDate = $subscription->end_date->copy()->addDay();
    $expectedEndDate = $expectedStartDate->copy()->addDays($plan->duration_in_days);

    expect($newSubscription->status)->toBe('pending_payment')
        ->and($newSubscription->start_date->toDateString())->toBe($expectedStartDate->toDateString())
        ->and($newSubscription->end_date->toDateString())->toBe($expectedEndDate->toDateString());

    // Invoice should be generated
    expect($newSubscription->invoices()->count())->toBe(1);

    // Old subscription should still be active (will be marked expired after payment)
    $subscription->refresh();
    expect($subscription->status)->toBe('active');
});

it('can cancel a subscription', function () {
    $member = Member::factory()->create();
    $plan = MembershipPlan::factory()->create();
    $subscription = Subscription::factory()->create([
        'member_id' => $member->id,
        'membership_plan_id' => $plan->id,
        'status' => 'active',
    ]);

    $response = $this->post("/subscriptions/{$subscription->id}/cancel");

    $response->assertRedirect("/members/{$member->id}");

    $subscription->refresh();
    expect($subscription->status)->toBe('cancelled')
        ->and($subscription->cancelled_at)->not->toBeNull();
});

it('requires authentication to access membership plans', function () {
    auth()->logout();

    $this->get('/membership-plans')->assertRedirect('/login');
    $this->get('/membership-plans/create')->assertRedirect('/login');
    $this->post('/membership-plans', [])->assertRedirect('/login');
});

it('requires authentication to manage subscriptions', function () {
    auth()->logout();
    $member = Member::factory()->create();

    $this->get("/members/{$member->id}/subscriptions/create")->assertRedirect('/login');
    $this->post('/subscriptions', [])->assertRedirect('/login');
});
