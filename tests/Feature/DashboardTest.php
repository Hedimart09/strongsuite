<?php

use App\Models\Attendance;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->admin()->create();
    $this->actingAs($this->user);
});

test('guests are redirected to the login page', function () {
    auth()->logout();
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $response = $this->get(route('dashboard'));
    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->has('metrics')
        ->has('recent_members')
        ->has('charts')
    );
});

test('it displays active members count', function () {
    Member::factory()->count(5)->active()->create();
    Member::factory()->count(3)->inactive()->create();

    $response = $this->get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->where('metrics.active_members', 5)
        ->where('metrics.total_members', 8)
    );
});

test('it displays today check-ins count', function () {
    $members = Member::factory()->count(3)->active()->create();

    // Today's check-ins
    foreach ($members as $member) {
        Attendance::factory()->create([
            'member_id' => $member->id,
            'check_in_time' => now(),
        ]);
    }

    // Yesterday's check-ins (should not be counted)
    Attendance::factory()->create([
        'member_id' => $members->first()->id,
        'check_in_time' => now()->subDay(),
    ]);

    $response = $this->get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->where('metrics.today_check_ins', 3)
    );
});

test('it displays this month check-ins count', function () {
    $members = Member::factory()->count(5)->active()->create();

    // This month's check-ins
    foreach ($members as $member) {
        Attendance::factory()->create([
            'member_id' => $member->id,
            'check_in_time' => now(),
        ]);
    }

    // Last month's check-ins (should not be counted)
    Attendance::factory()->create([
        'member_id' => $members->first()->id,
        'check_in_time' => now()->subMonth(),
    ]);

    $response = $this->get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->where('metrics.month_check_ins', 5)
    );
});

test('it displays today revenue', function () {
    $member = Member::factory()->active()->create();

    // Today's payments
    Payment::factory()->count(3)->create([
        'member_id' => $member->id,
        'amount' => 10000, // GHS 100.00
        'status' => 'completed',
        'payment_date' => now(),
    ]);

    // Yesterday's payment (should not be counted)
    Payment::factory()->create([
        'member_id' => $member->id,
        'amount' => 5000,
        'status' => 'completed',
        'payment_date' => now()->subDay(),
    ]);

    $response = $this->get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->where('metrics.today_revenue', 30000) // 3 * 10000
    );
});

test('it displays this month revenue', function () {
    $member = Member::factory()->active()->create();

    // This month's payments
    Payment::factory()->count(5)->create([
        'member_id' => $member->id,
        'amount' => 10000,
        'status' => 'completed',
        'payment_date' => now(),
    ]);

    // Last month's payment (should not be counted)
    Payment::factory()->create([
        'member_id' => $member->id,
        'amount' => 10000,
        'status' => 'completed',
        'payment_date' => now()->subMonth(),
    ]);

    $response = $this->get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->where('metrics.month_revenue', 50000) // 5 * 10000
    );
});

test('it only counts completed payments in revenue', function () {
    $member = Member::factory()->active()->create();

    Payment::factory()->create([
        'member_id' => $member->id,
        'amount' => 10000,
        'status' => 'completed',
        'payment_date' => now(),
    ]);

    Payment::factory()->create([
        'member_id' => $member->id,
        'amount' => 10000,
        'status' => 'pending',
        'payment_date' => now(),
    ]);

    $response = $this->get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->where('metrics.today_revenue', 10000)
    );
});

test('it displays active subscriptions count', function () {
    $plan = MembershipPlan::factory()->active()->create();
    $members = Member::factory()->count(4)->active()->create();

    // Active subscriptions
    Subscription::factory()->count(3)->create([
        'member_id' => fn () => $members->shift()->id,
        'membership_plan_id' => $plan->id,
        'status' => 'active',
        'start_date' => now()->subDays(10),
        'end_date' => now()->addDays(20),
    ]);

    // Expired subscription
    Subscription::factory()->create([
        'member_id' => $members->first()->id,
        'membership_plan_id' => $plan->id,
        'status' => 'active',
        'start_date' => now()->subDays(30),
        'end_date' => now()->subDay(),
    ]);

    $response = $this->get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->where('metrics.active_subscriptions', 3)
    );
});

test('it displays expiring soon subscriptions count', function () {
    $plan = MembershipPlan::factory()->active()->create();
    $members = Member::factory()->count(3)->active()->create();

    // Expiring within 7 days
    Subscription::factory()->count(2)->create([
        'member_id' => fn () => $members->shift()->id,
        'membership_plan_id' => $plan->id,
        'status' => 'active',
        'start_date' => now()->subDays(20),
        'end_date' => now()->addDays(3),
    ]);

    // Not expiring soon
    Subscription::factory()->create([
        'member_id' => $members->first()->id,
        'membership_plan_id' => $plan->id,
        'status' => 'active',
        'start_date' => now()->subDays(10),
        'end_date' => now()->addDays(30),
    ]);

    $response = $this->get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->where('metrics.expiring_soon', 2)
    );
});

test('it displays recent members', function () {
    Member::factory()->count(10)->active()->create();

    $response = $this->get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->has('recent_members', 10)
        ->has('recent_members.0.id')
        ->has('recent_members.0.name')
        ->has('recent_members.0.member_id')
        ->has('recent_members.0.status')
    );
});

test('it provides revenue chart data for last 7 days', function () {
    $member = Member::factory()->active()->create();

    // Create payments for last 7 days
    for ($i = 0; $i < 7; $i++) {
        Payment::factory()->create([
            'member_id' => $member->id,
            'amount' => 10000,
            'status' => 'completed',
            'payment_date' => now()->subDays($i),
        ]);
    }

    $response = $this->get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->has('charts.revenue_by_day.labels', 7)
        ->has('charts.revenue_by_day.data', 7)
    );
});

test('it provides check-ins chart data for last 7 days', function () {
    $member = Member::factory()->active()->create();

    // Create check-ins for last 7 days
    for ($i = 0; $i < 7; $i++) {
        Attendance::factory()->create([
            'member_id' => $member->id,
            'check_in_time' => now()->subDays($i),
        ]);
    }

    $response = $this->get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->has('charts.check_ins_by_day.labels', 7)
        ->has('charts.check_ins_by_day.data', 7)
    );
});

test('it fills missing days with zero in revenue chart', function () {
    $member = Member::factory()->active()->create();

    // Only create payment for today
    Payment::factory()->create([
        'member_id' => $member->id,
        'amount' => 10000,
        'status' => 'completed',
        'payment_date' => now(),
    ]);

    $response = $this->get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->has('charts.revenue_by_day.labels', 7)
        ->has('charts.revenue_by_day.data', 7)
        ->where('charts.revenue_by_day.data.6', 10000) // Today should have value
        ->where('charts.revenue_by_day.data.5', 0) // Yesterday should be 0
    );
});

test('it counts distinct members for check-ins', function () {
    $member = Member::factory()->active()->create();

    // Multiple check-ins from same member on same day
    Attendance::factory()->count(3)->create([
        'member_id' => $member->id,
        'check_in_time' => now(),
    ]);

    $response = $this->get(route('dashboard'));

    // Should only count 1 unique member
    $response->assertInertia(fn ($page) => $page
        ->where('metrics.today_check_ins', 1)
    );
});
