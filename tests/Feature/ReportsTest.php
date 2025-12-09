<?php

use App\Models\Attendance;
use App\Models\Member;
use App\Models\Payment;
use App\Models\User;

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->admin()->create();
    $this->actingAs($this->user);
});

test('guests are redirected to login', function () {
    auth()->logout();
    $response = $this->get(route('reports'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit reports page', function () {
    $response = $this->get(route('reports'));
    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Reports/Index')
        ->has('reportType')
        ->has('startDate')
        ->has('endDate')
        ->has('reportData')
    );
});

test('it defaults to attendance report', function () {
    $response = $this->get(route('reports'));
    $response->assertInertia(fn ($page) => $page
        ->where('reportType', 'attendance')
    );
});

test('it accepts report type parameter', function () {
    $response = $this->get(route('reports', ['type' => 'revenue']));
    $response->assertInertia(fn ($page) => $page
        ->where('reportType', 'revenue')
    );
});

test('it defaults to current month date range', function () {
    $response = $this->get(route('reports'));
    $response->assertInertia(fn ($page) => $page
        ->where('startDate', now()->startOfMonth()->format('Y-m-d'))
        ->where('endDate', now()->format('Y-m-d'))
    );
});

test('it accepts custom date range', function () {
    $startDate = '2024-01-01';
    $endDate = '2024-01-31';

    $response = $this->get(route('reports', [
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]));

    $response->assertInertia(fn ($page) => $page
        ->where('startDate', $startDate)
        ->where('endDate', $endDate)
    );
});

// Attendance Report Tests
test('attendance report includes total check-ins', function () {
    $members = Member::factory()->count(3)->active()->create();

    foreach ($members as $member) {
        Attendance::factory()->create([
            'member_id' => $member->id,
            'check_in_time' => now(),
        ]);
    }

    $response = $this->get(route('reports', ['type' => 'attendance']));

    $response->assertInertia(fn ($page) => $page
        ->where('reportData.summary.total_check_ins', 3)
    );
});

test('attendance report includes unique members count', function () {
    $member = Member::factory()->active()->create();

    // Same member checks in 3 times
    Attendance::factory()->count(3)->create([
        'member_id' => $member->id,
        'check_in_time' => now(),
    ]);

    $response = $this->get(route('reports', ['type' => 'attendance']));

    $response->assertInertia(fn ($page) => $page
        ->where('reportData.summary.unique_members', 1)
    );
});

test('attendance report calculates average check-ins per day', function () {
    $member = Member::factory()->active()->create();

    // 7 check-ins over 7 days = 1 per day
    for ($i = 0; $i < 7; $i++) {
        Attendance::factory()->create([
            'member_id' => $member->id,
            'check_in_time' => now()->subDays($i),
        ]);
    }

    $response = $this->get(route('reports', [
        'type' => 'attendance',
        'start_date' => now()->subDays(6)->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
    ]));

    $response->assertInertia(fn ($page) => $page
        ->where('reportData.summary.avg_check_ins_per_day', 1)
    );
});

test('attendance report includes top members', function () {
    $member1 = Member::factory()->active()->create();
    $member2 = Member::factory()->active()->create();

    Attendance::factory()->count(5)->create([
        'member_id' => $member1->id,
        'check_in_time' => now(),
    ]);

    Attendance::factory()->count(3)->create([
        'member_id' => $member2->id,
        'check_in_time' => now(),
    ]);

    $response = $this->get(route('reports', ['type' => 'attendance']));

    $response->assertInertia(fn ($page) => $page
        ->has('reportData.top_members', 2)
        ->where('reportData.top_members.0.check_in_count', 5)
        ->where('reportData.top_members.1.check_in_count', 3)
    );
});

test('attendance report includes peak hours', function () {
    $member = Member::factory()->active()->create();

    // Create check-ins at different hours
    Attendance::factory()->create([
        'member_id' => $member->id,
        'check_in_time' => now()->setTime(9, 0),
    ]);

    Attendance::factory()->create([
        'member_id' => $member->id,
        'check_in_time' => now()->setTime(9, 30),
    ]);

    $response = $this->get(route('reports', ['type' => 'attendance']));

    $response->assertInertia(fn ($page) => $page
        ->has('reportData.peak_hours')
    );
});

// Revenue Report Tests
test('revenue report includes total revenue', function () {
    $member = Member::factory()->active()->create();

    Payment::factory()->count(3)->create([
        'member_id' => $member->id,
        'amount' => 10000,
        'status' => 'completed',
        'payment_date' => now(),
    ]);

    $response = $this->get(route('reports', ['type' => 'revenue']));

    $response->assertInertia(fn ($page) => $page
        ->where('reportData.summary.total_revenue', 30000)
    );
});

test('revenue report only counts completed payments', function () {
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

    $response = $this->get(route('reports', ['type' => 'revenue']));

    $response->assertInertia(fn ($page) => $page
        ->where('reportData.summary.total_revenue', 10000)
        ->where('reportData.summary.total_payments', 1)
    );
});

test('revenue report includes revenue by payment method', function () {
    $member = Member::factory()->active()->create();

    Payment::factory()->create([
        'member_id' => $member->id,
        'amount' => 10000,
        'status' => 'completed',
        'payment_method' => 'cash',
        'payment_date' => now(),
    ]);

    Payment::factory()->create([
        'member_id' => $member->id,
        'amount' => 15000,
        'status' => 'completed',
        'payment_method' => 'card',
        'payment_date' => now(),
    ]);

    $response = $this->get(route('reports', ['type' => 'revenue']));

    $response->assertInertia(fn ($page) => $page
        ->has('reportData.revenue_by_method', 2)
    );
});

test('revenue report includes top paying members', function () {
    $member1 = Member::factory()->active()->create();
    $member2 = Member::factory()->active()->create();

    Payment::factory()->create([
        'member_id' => $member1->id,
        'amount' => 50000,
        'status' => 'completed',
        'payment_date' => now(),
    ]);

    Payment::factory()->create([
        'member_id' => $member2->id,
        'amount' => 30000,
        'status' => 'completed',
        'payment_date' => now(),
    ]);

    $response = $this->get(route('reports', ['type' => 'revenue']));

    $response->assertInertia(fn ($page) => $page
        ->has('reportData.top_paying_members', 2)
        ->where('reportData.top_paying_members.0.total_paid', 50000)
        ->where('reportData.top_paying_members.1.total_paid', 30000)
    );
});

// Members Report Tests
test('members report includes new members count', function () {
    Member::factory()->count(5)->active()->create([
        'created_at' => now(),
    ]);

    Member::factory()->count(3)->active()->create([
        'created_at' => now()->subMonth(),
    ]);

    $response = $this->get(route('reports', [
        'type' => 'members',
        'start_date' => now()->startOfMonth()->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
    ]));

    $response->assertInertia(fn ($page) => $page
        ->where('reportData.summary.new_members', 5)
    );
});

test('members report includes active and inactive members', function () {
    Member::factory()->count(10)->active()->create();
    Member::factory()->count(3)->inactive()->create();

    $response = $this->get(route('reports', ['type' => 'members']));

    $response->assertInertia(fn ($page) => $page
        ->where('reportData.summary.active_members', 10)
        ->where('reportData.summary.inactive_members', 3)
        ->where('reportData.summary.total_members', 13)
    );
});

test('members report includes recent members list', function () {
    Member::factory()->count(5)->active()->create([
        'created_at' => now(),
    ]);

    $response = $this->get(route('reports', [
        'type' => 'members',
        'start_date' => now()->startOfMonth()->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
    ]));

    $response->assertInertia(fn ($page) => $page
        ->has('reportData.recent_members', 5)
        ->has('reportData.recent_members.0.id')
        ->has('reportData.recent_members.0.name')
        ->has('reportData.recent_members.0.email')
    );
});

test('reports respect date range filtering', function () {
    $member = Member::factory()->active()->create();

    // Payment in range
    Payment::factory()->create([
        'member_id' => $member->id,
        'amount' => 10000,
        'status' => 'completed',
        'payment_date' => '2024-01-15',
    ]);

    // Payment outside range
    Payment::factory()->create([
        'member_id' => $member->id,
        'amount' => 10000,
        'status' => 'completed',
        'payment_date' => '2024-02-15',
    ]);

    $response = $this->get(route('reports', [
        'type' => 'revenue',
        'start_date' => '2024-01-01',
        'end_date' => '2024-01-31',
    ]));

    $response->assertInertia(fn ($page) => $page
        ->where('reportData.summary.total_revenue', 10000)
        ->where('reportData.summary.total_payments', 1)
    );
});
