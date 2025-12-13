<?php

use App\Mail\MemberPinMail;
use App\Models\Member;
use App\Models\MemberPinToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a member with a known PIN
    $this->member = Member::factory()->create([
        'pin' => Hash::make('1234'),
        'status' => 'active',
    ]);
});

// Member Login Tests
it('can view member login page', function () {
    $response = $this->get('/member/login');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('Member/Login'));
});

it('can login with valid credentials', function () {
    $response = $this->post('/member/login', [
        'member_id' => $this->member->member_id,
        'pin' => '1234',
    ]);

    $response->assertRedirect('/member/dashboard');
    expect(auth('member')->check())->toBeTrue();
    expect(auth('member')->id())->toBe($this->member->id);
});

it('cannot login with invalid member id', function () {
    $response = $this->post('/member/login', [
        'member_id' => 'INVALID-ID',
        'pin' => '1234',
    ]);

    $response->assertSessionHasErrors('member_id');
    expect(auth('member')->check())->toBeFalse();
});

it('cannot login with invalid pin', function () {
    $response = $this->post('/member/login', [
        'member_id' => $this->member->member_id,
        'pin' => '9999',
    ]);

    $response->assertSessionHasErrors('member_id');
    expect(auth('member')->check())->toBeFalse();
});

it('cannot login with inactive account', function () {
    $this->member->update(['status' => 'inactive']);

    $response = $this->post('/member/login', [
        'member_id' => $this->member->member_id,
        'pin' => '1234',
    ]);

    $response->assertSessionHasErrors('member_id');
    expect(auth('member')->check())->toBeFalse();
});

it('validates pin is required', function () {
    $response = $this->post('/member/login', [
        'member_id' => $this->member->member_id,
    ]);

    $response->assertSessionHasErrors('pin');
});

it('validates pin is 4 digits', function () {
    $response = $this->post('/member/login', [
        'member_id' => $this->member->member_id,
        'pin' => '12',
    ]);

    $response->assertSessionHasErrors('pin');
});

it('can logout successfully', function () {
    auth('member')->login($this->member);

    $response = $this->post('/member/logout');

    $response->assertRedirect('/member/login');
    expect(auth('member')->check())->toBeFalse();
});

// Member Dashboard Tests
it('can view dashboard when authenticated', function () {
    auth('member')->login($this->member);

    $response = $this->get('/member/dashboard');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Member/Dashboard')
        ->has('member')
        ->where('member.id', $this->member->id));
});

it('cannot view dashboard without authentication', function () {
    $response = $this->get('/member/dashboard');

    $response->assertRedirect('/member/login');
});

// Member Check-In Tests
it('can view check-in page when authenticated', function () {
    auth('member')->login($this->member);

    $response = $this->get('/member/check-in');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('Member/CheckIn'));
});

it('cannot view check-in page without authentication', function () {
    $response = $this->get('/member/check-in');

    $response->assertRedirect('/member/login');
});

it('can check in successfully', function () {
    auth('member')->login($this->member);

    $response = $this->post('/member/check-in');

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('attendances', [
        'member_id' => $this->member->id,
        'check_in_method' => 'manual',
    ]);
});

it('can check out when already checked in', function () {
    auth('member')->login($this->member);

    // First check-in
    $this->post('/member/check-in');

    // Check-out
    $response = $this->post('/member/check-in');

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $attendance = $this->member->attendances()->first();
    expect($attendance->check_out_time)->not->toBeNull();
});

it('cannot check in without authentication', function () {
    $response = $this->post('/member/check-in');

    $response->assertRedirect('/member/login');
});

// PIN Generation and Email Tests
it('sends pin email when creating new member', function () {
    Mail::fake();

    $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    $admin = \App\Models\User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this->actingAs($admin)->post('/members', [
        'name' => 'Test Member',
        'email' => 'test@example.com',
        'phone' => '0241234567',
        'gender' => 'male',
        'date_of_birth' => '1990-01-01',
        'emergency_contact_name' => 'John Doe',
        'emergency_contact_phone' => '0241234568',
    ]);

    $response->assertRedirect();

    $member = Member::where('email', 'test@example.com')->first();
    expect($member->pin)->not->toBeNull();
    expect($member->pin)->not->toBe(''); // PIN should be hashed

    // Check that email was sent
    Mail::assertSent(MemberPinMail::class, function ($mail) use ($member) {
        return $mail->hasTo('test@example.com') &&
               $mail->member->id === $member->id;
    });

    // Check that PIN token was created
    $this->assertDatabaseHas('member_pin_tokens', [
        'member_id' => $member->id,
    ]);
});

// PIN Viewing Tests
it('can view pin with valid token', function () {
    $pinToken = MemberPinToken::create([
        'member_id' => $this->member->id,
        'token' => 'test-token-123',
        'pin' => '1234',
        'expires_at' => now()->addHours(48),
    ]);

    $response = $this->get('/member/pin/test-token-123');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Member/ViewPin')
        ->where('pin', '1234')
        ->where('memberId', $this->member->member_id));
});

it('marks pin token as viewed when accessed', function () {
    $pinToken = MemberPinToken::create([
        'member_id' => $this->member->id,
        'token' => 'test-token-456',
        'pin' => '5678',
        'expires_at' => now()->addHours(48),
    ]);

    expect($pinToken->viewed_at)->toBeNull();

    $this->get('/member/pin/test-token-456');

    $pinToken->refresh();
    expect($pinToken->viewed_at)->not->toBeNull();
});

it('cannot view pin with invalid token', function () {
    $response = $this->get('/member/pin/invalid-token');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Member/ViewPin')
        ->where('pin', null)
        ->where('error', 'Invalid or expired link. Please contact the gym for assistance.'));
});

it('cannot view pin with expired token', function () {
    $pinToken = MemberPinToken::create([
        'member_id' => $this->member->id,
        'token' => 'expired-token',
        'pin' => '9999',
        'expires_at' => now()->subHours(1),
    ]);

    $response = $this->get('/member/pin/expired-token');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Member/ViewPin')
        ->where('pin', null)
        ->where('error', 'This link has expired. Please contact the gym for a new PIN.'));
});

// Member Guard Isolation
it('member authentication is isolated from staff authentication', function () {
    // Login as member
    auth('member')->login($this->member);

    // Check member is authenticated
    expect(auth('member')->check())->toBeTrue();

    // Check staff is not authenticated
    expect(auth('web')->check())->toBeFalse();
});
