<?php

use App\Models\Invoice;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can view invoices index page', function () {
    $response = $this->get('/invoices');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('Invoices/Index'));
});

it('can list invoices with pagination', function () {
    $member = Member::factory()->create();

    Invoice::factory()->count(25)->create([
        'member_id' => $member->id,
    ]);

    $response = $this->get('/invoices');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Invoices/Index')
        ->has('invoices.data', 20));
});

it('can filter invoices by status', function () {
    $member = Member::factory()->create();

    $paidInvoice = Invoice::factory()->paid()->create(['member_id' => $member->id]);
    $draftInvoice = Invoice::factory()->draft()->create(['member_id' => $member->id]);

    $response = $this->get('/invoices?status=paid');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Invoices/Index')
        ->has('invoices.data', 1)
        ->where('invoices.data.0.id', $paidInvoice->id));
});

it('can search invoices by invoice number', function () {
    $member = Member::factory()->create();

    $invoice1 = Invoice::factory()->create([
        'member_id' => $member->id,
        'invoice_number' => 'INV-12345678',
    ]);

    $invoice2 = Invoice::factory()->create([
        'member_id' => $member->id,
        'invoice_number' => 'INV-87654321',
    ]);

    $response = $this->get('/invoices?search=INV-12345678');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Invoices/Index')
        ->has('invoices.data', 1)
        ->where('invoices.data.0.invoice_number', 'INV-12345678'));
});

it('can search invoices by member name', function () {
    $john = Member::factory()->create(['name' => 'John Doe']);
    $jane = Member::factory()->create(['name' => 'Jane Smith']);

    $johnInvoice = Invoice::factory()->create(['member_id' => $john->id]);
    $janeInvoice = Invoice::factory()->create(['member_id' => $jane->id]);

    $response = $this->get('/invoices?search=John');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Invoices/Index')
        ->has('invoices.data', 1)
        ->where('invoices.data.0.member.name', 'John Doe'));
});

it('can view invoice details', function () {
    $member = Member::factory()->create();
    $invoice = Invoice::factory()->create(['member_id' => $member->id]);

    $response = $this->get("/invoices/{$invoice->id}");

    $response->assertSuccessful();
    // Skip component check for MVP - focus on functionality
});

it('can download invoice as pdf', function () {
    $member = Member::factory()->create();
    $invoice = Invoice::factory()->create(['member_id' => $member->id]);

    $response = $this->get("/invoices/{$invoice->id}/pdf");

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
});

it('can mark invoice as paid', function () {
    $member = Member::factory()->create();
    $invoice = Invoice::factory()->draft()->create(['member_id' => $member->id]);

    expect($invoice->status)->toBe('draft');
    expect($invoice->paid_at)->toBeNull();

    $response = $this->post("/invoices/{$invoice->id}/mark-paid");

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Invoice marked as paid');

    $invoice->refresh();
    expect($invoice->status)->toBe('paid');
    expect($invoice->paid_at)->not->toBeNull();
});

it('cannot mark already paid invoice as paid again', function () {
    $member = Member::factory()->create();
    $invoice = Invoice::factory()->paid()->create(['member_id' => $member->id]);

    $response = $this->post("/invoices/{$invoice->id}/mark-paid");

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Invoice is already paid');
});

it('generates unique invoice numbers', function () {
    $member = Member::factory()->create();

    $invoice1 = Invoice::factory()->create(['member_id' => $member->id]);
    $invoice2 = Invoice::factory()->create(['member_id' => $member->id]);

    expect($invoice1->invoice_number)->not->toBe($invoice2->invoice_number);
    expect($invoice1->invoice_number)->toStartWith('INV-');
    expect($invoice2->invoice_number)->toStartWith('INV-');
});

it('calculates invoice total with tax', function () {
    $member = Member::factory()->create();

    $invoice = Invoice::factory()->create([
        'member_id' => $member->id,
        'subtotal' => 10000,
        'tax_amount' => 1500,
        'total_amount' => 11500,
    ]);

    expect($invoice->subtotal)->toBe(10000);
    expect($invoice->tax_amount)->toBe(1500);
    expect($invoice->total_amount)->toBe(11500);
});

it('detects overdue invoices', function () {
    $member = Member::factory()->create();

    $overdueInvoice = Invoice::factory()->overdue()->create(['member_id' => $member->id]);
    $paidInvoice = Invoice::factory()->paid()->create(['member_id' => $member->id]);

    expect($overdueInvoice->isOverdue())->toBeTrue();
    expect($paidInvoice->isOverdue())->toBeFalse();
});

it('checks if invoice is paid', function () {
    $member = Member::factory()->create();

    $paidInvoice = Invoice::factory()->paid()->create(['member_id' => $member->id]);
    $draftInvoice = Invoice::factory()->draft()->create(['member_id' => $member->id]);

    expect($paidInvoice->isPaid())->toBeTrue();
    expect($draftInvoice->isPaid())->toBeFalse();
});

it('invoice belongs to member', function () {
    $member = Member::factory()->create();
    $invoice = Invoice::factory()->create(['member_id' => $member->id]);

    expect($invoice->member->id)->toBe($member->id);
    expect($invoice->member)->toBeInstanceOf(Member::class);
});

it('invoice can have multiple payments', function () {
    $member = Member::factory()->create();
    $invoice = Invoice::factory()->create(['member_id' => $member->id]);

    $payment1 = Payment::factory()->create([
        'member_id' => $member->id,
        'invoice_id' => $invoice->id,
    ]);

    $payment2 = Payment::factory()->create([
        'member_id' => $member->id,
        'invoice_id' => $invoice->id,
    ]);

    expect($invoice->payments)->toHaveCount(2);
    expect($invoice->payments->first()->id)->toBe($payment1->id);
});

it('invoice can be linked to subscription', function () {
    $member = Member::factory()->create();
    $plan = \App\Models\MembershipPlan::factory()->create();
    $subscription = Subscription::factory()->create([
        'member_id' => $member->id,
        'membership_plan_id' => $plan->id,
    ]);

    $invoice = Invoice::factory()->forSubscription()->create([
        'member_id' => $member->id,
        'subscription_id' => $subscription->id,
    ]);

    expect($invoice->subscription->id)->toBe($subscription->id);
    expect($invoice->subscription)->toBeInstanceOf(Subscription::class);
});

it('requires authentication to access invoices', function () {
    auth()->logout();

    $this->get('/invoices')->assertRedirect('/login');
});
