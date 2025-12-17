<?php

use App\Models\Invoice;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->admin()->create();
    $this->actingAs($this->user);
});

it('displays finance dashboard', function () {
    $response = $this->get('/finance');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Finance/Dashboard')
            ->has('metrics')
            ->has('filters'));
});

it('calculates total revenue correctly', function () {
    $member = Member::factory()->create();

    Payment::factory()->completed()->create(['member_id' => $member->id, 'amount' => 50000]);
    Payment::factory()->completed()->create(['member_id' => $member->id, 'amount' => 30000]);
    Payment::factory()->pending()->create(['member_id' => $member->id, 'amount' => 20000]); // Should not count

    $response = $this->get('/finance');

    $response->assertInertia(fn ($page) => $page
        ->where('metrics.total_revenue', 80000));
});

it('calculates today revenue correctly', function () {
    $member = Member::factory()->create();

    Payment::factory()->completed()->create([
        'member_id' => $member->id,
        'amount' => 50000,
        'payment_date' => today(),
    ]);
    Payment::factory()->completed()->create([
        'member_id' => $member->id,
        'amount' => 30000,
        'payment_date' => today()->subDay(),
    ]); // Should not count

    $response = $this->get('/finance');

    $response->assertInertia(fn ($page) => $page
        ->where('metrics.today_revenue', 50000));
});

it('calculates month revenue correctly', function () {
    $member = Member::factory()->create();

    Payment::factory()->completed()->create([
        'member_id' => $member->id,
        'amount' => 100000,
        'payment_date' => now(),
    ]);
    Payment::factory()->completed()->create([
        'member_id' => $member->id,
        'amount' => 50000,
        'payment_date' => now()->subMonth(),
    ]); // Should not count in this month

    $response = $this->get('/finance');

    $response->assertInertia(fn ($page) => $page
        ->where('metrics.month_revenue', 100000)
        ->where('metrics.last_month_revenue', 50000));
});

it('calculates outstanding and overdue invoices correctly', function () {
    $member = Member::factory()->create();

    // Overdue invoice
    Invoice::factory()->create([
        'member_id' => $member->id,
        'status' => 'sent',
        'total_amount' => 50000,
        'due_date' => today()->subDays(5),
    ]);

    // Invoice due soon (not overdue)
    Invoice::factory()->create([
        'member_id' => $member->id,
        'status' => 'sent',
        'total_amount' => 30000,
        'due_date' => today()->addDays(3),
    ]);

    // Paid invoice (should not count)
    Invoice::factory()->paid()->create([
        'member_id' => $member->id,
        'total_amount' => 20000,
        'due_date' => today()->subDays(2),
    ]);

    $response = $this->get('/finance');

    $response->assertInertia(fn ($page) => $page
        ->where('metrics.outstanding_amount', 80000)
        ->where('metrics.overdue_count', 1)
        ->where('metrics.overdue_amount', 50000)
        ->where('metrics.due_soon_count', 1));
});

it('calculates payment success rate correctly', function () {
    $member = Member::factory()->create();

    Payment::factory()->completed()->count(8)->create(['member_id' => $member->id]);
    Payment::factory()->failed()->count(2)->create(['member_id' => $member->id]);

    $response = $this->get('/finance');

    $response->assertInertia(fn ($page) => $page
        ->where('metrics.completed_payments', 8)
        ->where('metrics.failed_payments', 2)
        ->where('metrics.success_rate', 80));
});

it('calculates month over month growth correctly', function () {
    $member = Member::factory()->create();

    // This month: 120,000
    Payment::factory()->completed()->create([
        'member_id' => $member->id,
        'amount' => 120000,
        'payment_date' => now(),
    ]);

    // Last month: 100,000
    Payment::factory()->completed()->create([
        'member_id' => $member->id,
        'amount' => 100000,
        'payment_date' => now()->subMonth(),
    ]);

    $response = $this->get('/finance');

    // Growth should be (120000 - 100000) / 100000 * 100 = 20%
    $response->assertInertia(fn ($page) => $page
        ->where('metrics.month_growth', 20));
});

it('groups revenue by payment method correctly', function () {
    $member = Member::factory()->create();

    Payment::factory()->completed()->create([
        'member_id' => $member->id,
        'payment_method' => 'cash',
        'amount' => 50000,
    ]);
    Payment::factory()->completed()->create([
        'member_id' => $member->id,
        'payment_method' => 'cash',
        'amount' => 30000,
    ]);
    Payment::factory()->completed()->create([
        'member_id' => $member->id,
        'payment_method' => 'mobile_money',
        'amount' => 20000,
    ]);

    $response = $this->get('/finance');

    $response->assertInertia(fn ($page) => $page
        ->where('revenue_by_method.cash', 80000)
        ->where('revenue_by_method.mobile_money', 20000));
});

it('groups revenue by payment gateway correctly', function () {
    $member = Member::factory()->create();

    Payment::factory()->completed()->create([
        'member_id' => $member->id,
        'payment_gateway' => 'paystack',
        'amount' => 50000,
    ]);
    Payment::factory()->completed()->create([
        'member_id' => $member->id,
        'payment_gateway' => 'flutterwave',
        'amount' => 30000,
    ]);

    $response = $this->get('/finance');

    $response->assertInertia(fn ($page) => $page
        ->where('revenue_by_gateway.paystack', 50000)
        ->where('revenue_by_gateway.flutterwave', 30000));
});

it('shows recent payments', function () {
    $member = Member::factory()->create();

    Payment::factory()->completed()->count(5)->create(['member_id' => $member->id]);

    $response = $this->get('/finance');

    $response->assertInertia(fn ($page) => $page
        ->has('recent_payments', 5));
});

it('shows overdue invoices with days overdue', function () {
    $member = Member::factory()->create();

    Invoice::factory()->create([
        'member_id' => $member->id,
        'status' => 'sent',
        'due_date' => today()->subDays(5),
    ]);

    $response = $this->get('/finance');

    $response->assertInertia(fn ($page) => $page
        ->has('overdue_invoices', 1)
        ->where('overdue_invoices.0.days_overdue', -5));
});

it('shows top paying members', function () {
    $member1 = Member::factory()->create(['name' => 'John Doe']);
    $member2 = Member::factory()->create(['name' => 'Jane Smith']);

    $paymentDate = now()->subDays(5);

    Payment::factory()->completed()->create([
        'member_id' => $member1->id,
        'amount' => 100000,
        'payment_date' => $paymentDate,
    ]);
    Payment::factory()->completed()->create([
        'member_id' => $member2->id,
        'amount' => 50000,
        'payment_date' => $paymentDate,
    ]);

    $response = $this->get('/finance');

    $response->assertInertia(fn ($page) => $page
        ->has('top_paying_members', 2)
        ->where('top_paying_members.0.total_paid', 100000)
        ->where('top_paying_members.1.total_paid', 50000));
});

it('filters data by date range', function () {
    $member = Member::factory()->create();

    // Payment within range
    Payment::factory()->completed()->create([
        'member_id' => $member->id,
        'amount' => 50000,
        'payment_date' => now()->subDays(5),
    ]);

    // Payment outside range
    Payment::factory()->completed()->create([
        'member_id' => $member->id,
        'amount' => 30000,
        'payment_date' => now()->subDays(60),
    ]);

    $startDate = now()->subDays(7)->format('Y-m-d');
    $endDate = now()->format('Y-m-d');

    $response = $this->get("/finance?start_date={$startDate}&end_date={$endDate}");

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('filters.start_date', $startDate)
            ->where('filters.end_date', $endDate)
            ->has('chart_data'));
});

it('calculates active subscriptions value correctly', function () {
    $member = Member::factory()->create();
    $plan1 = MembershipPlan::factory()->create(['price' => 100000]);
    $plan2 = MembershipPlan::factory()->create(['price' => 50000]);

    Subscription::factory()->create([
        'member_id' => $member->id,
        'membership_plan_id' => $plan1->id,
        'status' => 'active',
    ]);
    Subscription::factory()->create([
        'member_id' => $member->id,
        'membership_plan_id' => $plan2->id,
        'status' => 'active',
    ]);
    Subscription::factory()->create([
        'member_id' => $member->id,
        'membership_plan_id' => $plan1->id,
        'status' => 'expired',
    ]); // Should not count

    $response = $this->get('/finance');

    $response->assertInertia(fn ($page) => $page
        ->where('metrics.active_subscriptions_value', 150000));
});

it('shows members with overdue invoices', function () {
    $member1 = Member::factory()->create();
    $member2 = Member::factory()->create();

    Invoice::factory()->count(2)->create([
        'member_id' => $member1->id,
        'status' => 'sent',
        'total_amount' => 50000,
        'due_date' => today()->subDays(5),
    ]);
    Invoice::factory()->create([
        'member_id' => $member2->id,
        'status' => 'sent',
        'total_amount' => 30000,
        'due_date' => today()->subDays(3),
    ]);

    $response = $this->get('/finance');

    $response->assertInertia(fn ($page) => $page
        ->has('members_with_overdue', 2)
        ->where('members_with_overdue.0.overdue_count', 2)
        ->where('members_with_overdue.0.overdue_amount', 100000)
        ->where('members_with_overdue.1.overdue_count', 1)
        ->where('members_with_overdue.1.overdue_amount', 30000));
});

it('shows gateway performance metrics', function () {
    $member = Member::factory()->create();

    // Paystack: 8 completed, 2 failed = 80% success rate
    Payment::factory()->completed()->count(8)->create([
        'member_id' => $member->id,
        'payment_gateway' => 'paystack',
    ]);
    Payment::factory()->failed()->count(2)->create([
        'member_id' => $member->id,
        'payment_gateway' => 'paystack',
    ]);

    // Flutterwave: 9 completed, 1 failed = 90% success rate
    Payment::factory()->completed()->count(9)->create([
        'member_id' => $member->id,
        'payment_gateway' => 'flutterwave',
    ]);
    Payment::factory()->failed()->create([
        'member_id' => $member->id,
        'payment_gateway' => 'flutterwave',
    ]);

    $response = $this->get('/finance');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->has('gateway_performance', 2));
});
