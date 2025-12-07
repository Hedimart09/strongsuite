<?php

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    Storage::fake('public');
});

it('can display members index page', function () {
    $response = $this->get('/members');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('Members/Index'));
});

it('can display members create page', function () {
    $response = $this->get('/members/create');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('Members/Create'));
});

it('can register a new member', function () {
    $memberData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '0244123456',
        'date_of_birth' => '1990-01-01',
        'gender' => 'male',
        'address' => '123 Main St, Accra',
        'emergency_contact_name' => 'Jane Doe',
        'emergency_contact_phone' => '0244654321',
    ];

    $response = $this->post('/members', $memberData);

    $response->assertRedirect();
    $this->assertDatabaseHas('members', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'status' => 'active',
    ]);

    $member = Member::where('email', 'john@example.com')->first();
    expect($member->member_id)->not->toBeNull()
        ->and($member->qr_code)->not->toBeNull()
        ->and($member->status)->toBe('active');
});

it('can register member with photo', function () {
    $photo = UploadedFile::fake()->image('member.jpg');

    $memberData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '0244123456',
        'date_of_birth' => '1990-01-01',
        'gender' => 'male',
        'emergency_contact_name' => 'Jane Doe',
        'emergency_contact_phone' => '0244654321',
        'photo' => $photo,
    ];

    $response = $this->post('/members', $memberData);

    $response->assertRedirect();

    $member = Member::where('email', 'john@example.com')->first();
    expect($member->photo)->not->toBeNull();
    Storage::disk('public')->assertExists($member->photo);
});

it('validates required fields when registering member', function () {
    $response = $this->post('/members', []);

    $response->assertSessionHasErrors([
        'name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'emergency_contact_name',
        'emergency_contact_phone',
    ]);
});

it('validates unique email when registering member', function () {
    Member::factory()->create(['email' => 'existing@example.com']);

    $response = $this->post('/members', [
        'name' => 'John Doe',
        'email' => 'existing@example.com',
        'phone' => '0244123456',
        'date_of_birth' => '1990-01-01',
        'gender' => 'male',
        'emergency_contact_name' => 'Jane Doe',
        'emergency_contact_phone' => '0244654321',
    ]);

    $response->assertSessionHasErrors(['email']);
});

it('can display member profile', function () {
    $member = Member::factory()->create();

    $response = $this->get("/members/{$member->id}");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Members/Show')
        ->has('member'));
});

it('displays member profile with attendance history', function () {
    $member = Member::factory()->create();

    $attendance1 = \App\Models\Attendance::factory()->create([
        'member_id' => $member->id,
        'check_in_time' => now()->subHours(3),
        'check_out_time' => now()->subHours(2),
        'check_in_method' => 'qr_code',
    ]);

    $attendance2 = \App\Models\Attendance::factory()->create([
        'member_id' => $member->id,
        'check_in_time' => now()->subDay(),
        'check_out_time' => null,
        'check_in_method' => 'manual',
    ]);

    $response = $this->get("/members/{$member->id}");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Members/Show')
        ->has('member.attendances', 2)
        ->where('member.attendances.0.check_in_method', 'qr_code')
        ->where('member.attendances.1.check_in_method', 'manual'));
});

it('can display member edit page', function () {
    $member = Member::factory()->create();

    $response = $this->get("/members/{$member->id}/edit");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Members/Edit')
        ->has('member'));
});

it('can update member', function () {
    $member = Member::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $updateData = [
        'name' => 'New Name',
        'email' => 'new@example.com',
        'phone' => $member->phone,
        'date_of_birth' => $member->date_of_birth,
        'gender' => $member->gender,
        'emergency_contact_name' => $member->emergency_contact_name,
        'emergency_contact_phone' => $member->emergency_contact_phone,
        'status' => 'inactive',
    ];

    $response = $this->put("/members/{$member->id}", $updateData);

    $response->assertRedirect();
    $this->assertDatabaseHas('members', [
        'id' => $member->id,
        'name' => 'New Name',
        'email' => 'new@example.com',
        'status' => 'inactive',
    ]);
});

it('can update member with new photo', function () {
    $member = Member::factory()->create();
    $newPhoto = UploadedFile::fake()->image('new-photo.jpg');

    $updateData = [
        'name' => $member->name,
        'email' => $member->email,
        'phone' => $member->phone,
        'date_of_birth' => $member->date_of_birth,
        'gender' => $member->gender,
        'emergency_contact_name' => $member->emergency_contact_name,
        'emergency_contact_phone' => $member->emergency_contact_phone,
        'status' => $member->status,
        'photo' => $newPhoto,
    ];

    $response = $this->put("/members/{$member->id}", $updateData);

    $response->assertRedirect();
    $member->refresh();
    expect($member->photo)->not->toBeNull();
    Storage::disk('public')->assertExists($member->photo);
});

it('can delete member', function () {
    $member = Member::factory()->create();

    $response = $this->delete("/members/{$member->id}");

    $response->assertRedirect('/members');
    $this->assertDatabaseMissing('members', ['id' => $member->id]);
});

it('deletes member photo when deleting member', function () {
    $photo = UploadedFile::fake()->image('member.jpg');
    $member = Member::factory()->create();
    $photoPath = $photo->store('members/photos', 'public');
    $member->update(['photo' => $photoPath]);

    Storage::disk('public')->assertExists($photoPath);

    $this->delete("/members/{$member->id}");

    Storage::disk('public')->assertMissing($photoPath);
});

it('can display member qr code page', function () {
    $member = Member::factory()->create();

    $response = $this->get("/members/{$member->id}/qr-code");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Members/QrCode')
        ->has('member')
        ->has('qrCodeSvg'));
});

it('can search members by name', function () {
    Member::factory()->create(['name' => 'John Doe']);
    Member::factory()->create(['name' => 'Jane Smith']);

    $response = $this->get('/members?search=John');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Members/Index')
        ->where('members.data.0.name', 'John Doe'));
});

it('can filter members by status', function () {
    Member::factory()->active()->create(['name' => 'Active Member']);
    Member::factory()->inactive()->create(['name' => 'Inactive Member']);

    $response = $this->get('/members?status=active');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Members/Index')
        ->where('members.data.0.status', 'active'));
});

it('generates unique member id', function () {
    $member1 = Member::factory()->create();
    $member2 = Member::factory()->create();

    expect($member1->member_id)->not->toBe($member2->member_id)
        ->and($member1->member_id)->toStartWith('MEM-')
        ->and($member2->member_id)->toStartWith('MEM-');
});

it('generates unique qr code', function () {
    $member1 = Member::factory()->create();
    $member2 = Member::factory()->create();

    expect($member1->qr_code)->not->toBe($member2->qr_code)
        ->and($member1->qr_code)->toStartWith('QR-')
        ->and($member2->qr_code)->toStartWith('QR-');
});

it('requires authentication to access members', function () {
    auth()->logout();

    $this->get('/members')->assertRedirect('/login');
    $this->get('/members/create')->assertRedirect('/login');
    $this->post('/members', [])->assertRedirect('/login');
});
