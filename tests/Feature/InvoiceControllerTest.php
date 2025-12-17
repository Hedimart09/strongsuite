<?php

use App\Models\Invoice;
use App\Models\Member;
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

describe('index', function () {
    it('displays invoices list', function () {
        $member = Member::factory()->create();
        Invoice::factory()->count(3)->create(['member_id' => $member->id]);

        $response = $this->get('/invoices');

        $response->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Invoices/Index')
                ->has('invoices.data', 3));
    });

    it('filters invoices by status', function () {
        $member = Member::factory()->create();
        Invoice::factory()->count(2)->create(['member_id' => $member->id, 'status' => 'paid']);
        Invoice::factory()->create(['member_id' => $member->id, 'status' => 'draft']);

        $response = $this->get('/invoices?status=paid');

        $response->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Invoices/Index')
                ->has('invoices.data', 2)
                ->where('invoices.data.0.status', 'paid'));
    });

    it('searches invoices by invoice number', function () {
        $member = Member::factory()->create();
        $invoice = Invoice::factory()->create([
            'member_id' => $member->id,
            'invoice_number' => 'INV-12345678',
        ]);
        Invoice::factory()->create(['member_id' => $member->id, 'invoice_number' => 'INV-87654321']);

        $response = $this->get('/invoices?search=12345678');

        $response->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Invoices/Index')
                ->has('invoices.data', 1)
                ->where('invoices.data.0.invoice_number', 'INV-12345678'));
    });

    it('searches invoices by member name', function () {
        $member = Member::factory()->create(['name' => 'John Doe']);
        $otherMember = Member::factory()->create(['name' => 'Jane Smith']);

        Invoice::factory()->create(['member_id' => $member->id]);
        Invoice::factory()->create(['member_id' => $otherMember->id]);

        $response = $this->get('/invoices?search=John');

        $response->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Invoices/Index')
                ->has('invoices.data', 1));
    });

    it('paginates invoices', function () {
        $member = Member::factory()->create();
        Invoice::factory()->count(25)->create(['member_id' => $member->id]);

        $response = $this->get('/invoices');

        $response->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->has('invoices.data', 20)
                ->has('invoices.links'));
    });
});

describe('show', function () {
    it('displays invoice details', function () {
        $member = Member::factory()->create();
        $invoice = Invoice::factory()->create(['member_id' => $member->id]);

        $response = $this->get("/invoices/{$invoice->id}");

        $response->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Invoices/Show')
                ->where('invoice.id', $invoice->id)
                ->where('invoice.invoice_number', $invoice->invoice_number));
    });

    it('loads invoice relationships', function () {
        $member = Member::factory()->create();
        $plan = \App\Models\MembershipPlan::factory()->create();
        $subscription = Subscription::factory()->create([
            'member_id' => $member->id,
            'membership_plan_id' => $plan->id,
        ]);
        $invoice = Invoice::factory()->create([
            'member_id' => $member->id,
            'subscription_id' => $subscription->id,
        ]);

        $response = $this->get("/invoices/{$invoice->id}");

        $response->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->has('invoice.member')
                ->has('invoice.subscription')
                ->has('invoice.payments'));
    });
});

describe('create', function () {
    it('displays invoice creation form', function () {
        Member::factory()->count(3)->active()->create();

        $response = $this->get('/invoices/create');

        $response->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Invoices/Create')
                ->has('members', 3));
    });

    it('preselects member when member_id is provided', function () {
        $member = Member::factory()->create();

        $response = $this->get("/invoices/create?member_id={$member->id}");

        $response->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Invoices/Create')
                ->where('selected_member_id', $member->id));
    });

    it('only shows active members', function () {
        Member::factory()->count(2)->active()->create();
        Member::factory()->inactive()->create();

        $response = $this->get('/invoices/create');

        $response->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->has('members', 2));
    });
});

describe('store', function () {
    it('creates a new invoice', function () {
        $member = Member::factory()->create();

        $data = [
            'member_id' => $member->id,
            'items' => [
                ['description' => 'Monthly Membership', 'amount' => 50000],
            ],
            'tax_rate' => 0,
            'due_date' => now()->addDays(14)->format('Y-m-d'),
        ];

        $response = $this->post('/invoices', $data);

        $response->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'member_id' => $member->id,
            'subtotal' => 50000,
            'total_amount' => 50000,
            'status' => 'sent',
        ]);
    });

    it('creates invoice with multiple line items', function () {
        $member = Member::factory()->create();

        $data = [
            'member_id' => $member->id,
            'items' => [
                ['description' => 'Monthly Membership', 'amount' => 50000],
                ['description' => 'Personal Training', 'amount' => 30000],
            ],
            'tax_rate' => 0,
            'due_date' => now()->addDays(14)->format('Y-m-d'),
        ];

        $response = $this->post('/invoices', $data);

        $response->assertRedirect();

        $invoice = Invoice::where('member_id', $member->id)->first();

        expect($invoice)->not->toBeNull()
            ->and($invoice->subtotal)->toBe(80000)
            ->and($invoice->total_amount)->toBe(80000)
            ->and($invoice->metadata['items'])->toHaveCount(2);
    });

    it('calculates tax correctly', function () {
        $member = Member::factory()->create();

        $data = [
            'member_id' => $member->id,
            'items' => [
                ['description' => 'Monthly Membership', 'amount' => 100000],
            ],
            'tax_rate' => 15,
            'due_date' => now()->addDays(14)->format('Y-m-d'),
        ];

        $response = $this->post('/invoices', $data);

        $response->assertRedirect();

        $invoice = Invoice::where('member_id', $member->id)->first();

        expect($invoice->subtotal)->toBe(100000)
            ->and($invoice->tax_amount)->toBe(15000)
            ->and($invoice->total_amount)->toBe(115000);
    });

    it('generates unique invoice number', function () {
        $member = Member::factory()->create();

        $data = [
            'member_id' => $member->id,
            'items' => [
                ['description' => 'Membership', 'amount' => 50000],
            ],
            'due_date' => now()->addDays(14)->format('Y-m-d'),
        ];

        $this->post('/invoices', $data);
        $this->post('/invoices', $data);

        $invoices = Invoice::all();

        expect($invoices)->toHaveCount(2)
            ->and($invoices[0]->invoice_number)->not->toBe($invoices[1]->invoice_number)
            ->and($invoices[0]->invoice_number)->toStartWith('INV-')
            ->and($invoices[1]->invoice_number)->toStartWith('INV-');
    });

    it('stores notes when provided', function () {
        $member = Member::factory()->create();

        $data = [
            'member_id' => $member->id,
            'items' => [
                ['description' => 'Membership', 'amount' => 50000],
            ],
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'notes' => 'Payment due within 14 days',
        ];

        $this->post('/invoices', $data);

        $this->assertDatabaseHas('invoices', [
            'member_id' => $member->id,
            'notes' => 'Payment due within 14 days',
        ]);
    });

    it('links to subscription when provided', function () {
        $member = Member::factory()->create();
        $plan = \App\Models\MembershipPlan::factory()->create();
        $subscription = Subscription::factory()->create([
            'member_id' => $member->id,
            'membership_plan_id' => $plan->id,
        ]);

        $data = [
            'member_id' => $member->id,
            'subscription_id' => $subscription->id,
            'items' => [
                ['description' => 'Membership', 'amount' => 50000],
            ],
            'due_date' => now()->addDays(14)->format('Y-m-d'),
        ];

        $this->post('/invoices', $data);

        $this->assertDatabaseHas('invoices', [
            'member_id' => $member->id,
            'subscription_id' => $subscription->id,
        ]);
    });

    it('validates required fields', function () {
        $response = $this->post('/invoices', []);

        $response->assertSessionHasErrors(['member_id', 'items', 'due_date']);
    });

    it('validates member exists', function () {
        $data = [
            'member_id' => 99999,
            'items' => [
                ['description' => 'Membership', 'amount' => 50000],
            ],
            'due_date' => now()->addDays(14)->format('Y-m-d'),
        ];

        $response = $this->post('/invoices', $data);

        $response->assertSessionHasErrors(['member_id']);
    });

    it('validates items array is not empty', function () {
        $member = Member::factory()->create();

        $data = [
            'member_id' => $member->id,
            'items' => [],
            'due_date' => now()->addDays(14)->format('Y-m-d'),
        ];

        $response = $this->post('/invoices', $data);

        $response->assertSessionHasErrors(['items']);
    });

    it('validates item description is required', function () {
        $member = Member::factory()->create();

        $data = [
            'member_id' => $member->id,
            'items' => [
                ['amount' => 50000],
            ],
            'due_date' => now()->addDays(14)->format('Y-m-d'),
        ];

        $response = $this->post('/invoices', $data);

        $response->assertSessionHasErrors(['items.0.description']);
    });

    it('validates item amount is required', function () {
        $member = Member::factory()->create();

        $data = [
            'member_id' => $member->id,
            'items' => [
                ['description' => 'Membership'],
            ],
            'due_date' => now()->addDays(14)->format('Y-m-d'),
        ];

        $response = $this->post('/invoices', $data);

        $response->assertSessionHasErrors(['items.0.amount']);
    });
});

describe('downloadPdf', function () {
    it('generates pdf for invoice', function () {
        $member = Member::factory()->create();
        $invoice = Invoice::factory()->create(['member_id' => $member->id]);

        $response = $this->get("/invoices/{$invoice->id}/pdf");

        $response->assertSuccessful()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertDownload("{$invoice->invoice_number}.pdf");
    });

    it('includes invoice data in pdf', function () {
        $member = Member::factory()->create();
        $plan = \App\Models\MembershipPlan::factory()->create();
        $subscription = Subscription::factory()->create([
            'member_id' => $member->id,
            'membership_plan_id' => $plan->id,
        ]);
        $invoice = Invoice::factory()->create([
            'member_id' => $member->id,
            'subscription_id' => $subscription->id,
        ]);

        $response = $this->get("/invoices/{$invoice->id}/pdf");

        $response->assertSuccessful();
    });
});

describe('markAsPaid', function () {
    it('marks invoice as paid', function () {
        $member = Member::factory()->create();
        $invoice = Invoice::factory()->create([
            'member_id' => $member->id,
            'status' => 'sent',
            'paid_at' => null,
        ]);

        $response = $this->post("/invoices/{$invoice->id}/mark-paid");

        $response->assertRedirect();

        $invoice->refresh();

        expect($invoice->status)->toBe('paid')
            ->and($invoice->paid_at)->not->toBeNull();
    });

    it('does not mark already paid invoice', function () {
        $member = Member::factory()->create();
        $invoice = Invoice::factory()->paid()->create(['member_id' => $member->id]);

        $response = $this->post("/invoices/{$invoice->id}/mark-paid");

        $response->assertRedirect()
            ->assertSessionHas('error');
    });
});
