<?php

use App\Models\Attendance;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->admin()->create();
    $this->actingAs($this->user);
});

it('can view attendance index page', function () {
    $response = $this->get('/attendance');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('Attendance/Index'));
});

it('displays todays attendance by default', function () {
    $member = Member::factory()->create();

    $todayAttendance = Attendance::factory()->create([
        'member_id' => $member->id,
        'check_in_time' => now(),
    ]);

    $yesterdayAttendance = Attendance::factory()->create([
        'member_id' => $member->id,
        'check_in_time' => now()->subDay(),
    ]);

    $response = $this->get('/attendance');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Attendance/Index')
        ->has('attendances.data', 1)
        ->where('attendances.data.0.id', $todayAttendance->id)
    );
});

it('can filter attendance by date', function () {
    $member = Member::factory()->create();

    $targetDate = now()->subDays(3)->format('Y-m-d');

    $targetAttendance = Attendance::factory()->create([
        'member_id' => $member->id,
        'check_in_time' => $targetDate,
    ]);

    $otherAttendance = Attendance::factory()->create([
        'member_id' => $member->id,
        'check_in_time' => now(),
    ]);

    $response = $this->get('/attendance?date='.$targetDate);

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Attendance/Index')
        ->has('attendances.data', 1)
        ->where('attendances.data.0.id', $targetAttendance->id)
    );
});

it('can search attendance by member name', function () {
    $john = Member::factory()->create(['name' => 'John Doe']);
    $jane = Member::factory()->create(['name' => 'Jane Smith']);

    $johnAttendance = Attendance::factory()->create([
        'member_id' => $john->id,
        'check_in_time' => now(),
    ]);

    $janeAttendance = Attendance::factory()->create([
        'member_id' => $jane->id,
        'check_in_time' => now(),
    ]);

    $response = $this->get('/attendance?search=John');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Attendance/Index')
        ->has('attendances.data', 1)
        ->where('attendances.data.0.member.name', 'John Doe')
    );
});

it('can search attendance by member id', function () {
    $member1 = Member::factory()->create(['member_id' => 'MEM-12345678']);
    $member2 = Member::factory()->create(['member_id' => 'MEM-87654321']);

    $attendance1 = Attendance::factory()->create([
        'member_id' => $member1->id,
        'check_in_time' => now(),
    ]);

    $attendance2 = Attendance::factory()->create([
        'member_id' => $member2->id,
        'check_in_time' => now(),
    ]);

    $response = $this->get('/attendance?search=MEM-12345678');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Attendance/Index')
        ->has('attendances.data', 1)
        ->where('attendances.data.0.member.member_id', 'MEM-12345678')
    );
});

it('can view qr scan page', function () {
    $response = $this->get('/attendance/scan');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('Attendance/Scan'));
});

it('can check in member with qr code', function () {
    $member = Member::factory()->create([
        'qr_code' => 'QR-123456789012',
    ]);

    $response = $this->post('/attendance/check-in/qr', [
        'qr_code' => 'QR-123456789012',
    ]);

    $response->assertRedirect('/attendance');
    $response->assertSessionHas('success', "{$member->name} checked in successfully");

    $this->assertDatabaseHas('attendances', [
        'member_id' => $member->id,
        'check_in_method' => 'qr_code',
    ]);

    $attendance = Attendance::where('member_id', $member->id)->first();
    expect($attendance->check_out_time)->toBeNull();
});

it('can check out member with qr code when already checked in', function () {
    $member = Member::factory()->create([
        'qr_code' => 'QR-123456789012',
    ]);

    $attendance = Attendance::factory()->create([
        'member_id' => $member->id,
        'check_in_time' => now()->subHour(),
        'check_out_time' => null,
    ]);

    $response = $this->post('/attendance/check-in/qr', [
        'qr_code' => 'QR-123456789012',
    ]);

    $response->assertRedirect('/attendance');
    $response->assertSessionHas('success', "{$member->name} checked out successfully");

    $attendance->refresh();
    expect($attendance->check_out_time)->not->toBeNull();
});

it('returns error for invalid qr code', function () {
    $response = $this->post('/attendance/check-in/qr', [
        'qr_code' => 'INVALID-QR-CODE',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Invalid QR code');
});

it('validates qr code is required', function () {
    $response = $this->post('/attendance/check-in/qr', [
        'qr_code' => '',
    ]);

    $response->assertSessionHasErrors(['qr_code']);
});

it('can view manual check-in page', function () {
    $response = $this->get('/attendance/manual');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Attendance/Manual')
        ->has('members')
    );
});

it('only shows active members on manual check-in page', function () {
    $activeMember = Member::factory()->active()->create();
    $inactiveMember = Member::factory()->inactive()->create();

    $response = $this->get('/attendance/manual');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Attendance/Manual')
        ->has('members', 1)
        ->where('members.0.id', $activeMember->id)
    );
});

it('can check in member manually', function () {
    $member = Member::factory()->create();

    $response = $this->post('/attendance/check-in/manual', [
        'member_id' => $member->id,
    ]);

    $response->assertRedirect('/attendance');
    $response->assertSessionHas('success', "{$member->name} checked in successfully");

    $this->assertDatabaseHas('attendances', [
        'member_id' => $member->id,
        'check_in_method' => 'manual',
    ]);

    $attendance = Attendance::where('member_id', $member->id)->first();
    expect($attendance->check_out_time)->toBeNull();
});

it('can check out member manually when already checked in', function () {
    $member = Member::factory()->create();

    $attendance = Attendance::factory()->create([
        'member_id' => $member->id,
        'check_in_time' => now()->subHour(),
        'check_out_time' => null,
    ]);

    $response = $this->post('/attendance/check-in/manual', [
        'member_id' => $member->id,
    ]);

    $response->assertRedirect('/attendance');
    $response->assertSessionHas('success', "{$member->name} checked out successfully");

    $attendance->refresh();
    expect($attendance->check_out_time)->not->toBeNull();
});

it('validates member_id is required for manual check-in', function () {
    $response = $this->post('/attendance/check-in/manual', [
        'member_id' => '',
    ]);

    $response->assertSessionHasErrors(['member_id']);
});

it('validates member exists for manual check-in', function () {
    $response = $this->post('/attendance/check-in/manual', [
        'member_id' => 999999,
    ]);

    $response->assertSessionHasErrors(['member_id']);
});

it('can check out member from attendance log', function () {
    $member = Member::factory()->create();

    $attendance = Attendance::factory()->create([
        'member_id' => $member->id,
        'check_in_time' => now()->subHour(),
        'check_out_time' => null,
    ]);

    $response = $this->post("/attendance/{$attendance->id}/check-out");

    $response->assertRedirect('/attendance');
    $response->assertSessionHas('success', 'Checked out successfully');

    $attendance->refresh();
    expect($attendance->check_out_time)->not->toBeNull();
});

it('cannot check out already checked out attendance', function () {
    $member = Member::factory()->create();

    $attendance = Attendance::factory()->create([
        'member_id' => $member->id,
        'check_in_time' => now()->subHours(2),
        'check_out_time' => now()->subHour(),
    ]);

    $checkOutTime = $attendance->check_out_time;

    $response = $this->post("/attendance/{$attendance->id}/check-out");

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Already checked out');

    $attendance->refresh();
    expect($attendance->check_out_time->equalTo($checkOutTime))->toBeTrue();
});
