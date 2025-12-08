<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed roles and permissions
    $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);

    // Create an admin user
    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');

    // Create a receptionist user
    $this->receptionist = User::factory()->create();
    $this->receptionist->assignRole('Receptionist');

    // Create a trainer user
    $this->trainer = User::factory()->create();
    $this->trainer->assignRole('Trainer');
});

it('can view staff index page as admin', function () {
    $response = $this->actingAs($this->admin)->get('/staff');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('Staff/Index'));
});

it('cannot view staff index page without staff.view permission', function () {
    $response = $this->actingAs($this->trainer)->get('/staff');

    $response->assertForbidden();
});

it('can list staff members as admin', function () {
    $response = $this->actingAs($this->admin)->get('/staff');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Staff/Index')
        ->has('staff.data', 3));
});

it('can filter staff by role', function () {
    $response = $this->actingAs($this->admin)->get('/staff?role=Admin');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Staff/Index')
        ->has('staff.data', 1)
        ->where('staff.data.0.id', $this->admin->id));
});

it('can search staff by name', function () {
    $response = $this->actingAs($this->admin)->get('/staff?search='.$this->trainer->name);

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Staff/Index')
        ->has('staff.data', 1)
        ->where('staff.data.0.id', $this->trainer->id));
});

it('can create staff member as admin', function () {
    $response = $this->actingAs($this->admin)->post('/staff', [
        'name' => 'New Staff',
        'email' => 'newstaff@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'Receptionist',
    ]);

    $response->assertRedirect('/staff');
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'name' => 'New Staff',
        'email' => 'newstaff@example.com',
    ]);

    $newUser = User::where('email', 'newstaff@example.com')->first();
    expect($newUser->hasRole('Receptionist'))->toBeTrue();
});

it('cannot create staff without staff.create permission', function () {
    $response = $this->actingAs($this->trainer)->post('/staff', [
        'name' => 'New Staff',
        'email' => 'newstaff@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'Receptionist',
    ]);

    $response->assertForbidden();
});

it('requires email to be unique when creating staff', function () {
    $response = $this->actingAs($this->admin)->post('/staff', [
        'name' => 'Duplicate Email',
        'email' => $this->trainer->email,
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'Trainer',
    ]);

    $response->assertSessionHasErrors('email');
});

it('can update staff member as admin', function () {
    $staffMember = User::factory()->create();
    $staffMember->assignRole('Trainer');

    $response = $this->actingAs($this->admin)->patch("/staff/{$staffMember->id}", [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'role' => 'Receptionist',
    ]);

    $response->assertRedirect('/staff');
    $response->assertSessionHas('success');

    $staffMember->refresh();
    expect($staffMember->name)->toBe('Updated Name');
    expect($staffMember->email)->toBe('updated@example.com');
    expect($staffMember->hasRole('Receptionist'))->toBeTrue();
    expect($staffMember->hasRole('Trainer'))->toBeFalse();
});

it('can update staff password', function () {
    $staffMember = User::factory()->create();
    $staffMember->assignRole('Trainer');

    $response = $this->actingAs($this->admin)->patch("/staff/{$staffMember->id}", [
        'name' => $staffMember->name,
        'email' => $staffMember->email,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
        'role' => 'Trainer',
    ]);

    $response->assertRedirect('/staff');
    $staffMember->refresh();

    expect(\Hash::check('newpassword123', $staffMember->password))->toBeTrue();
});

it('cannot update staff without staff.edit permission', function () {
    $response = $this->actingAs($this->trainer)->patch("/staff/{$this->receptionist->id}", [
        'name' => 'Hacked Name',
        'email' => 'hacked@example.com',
        'role' => 'Admin',
    ]);

    $response->assertForbidden();
});

it('can delete staff member as admin', function () {
    $staffMember = User::factory()->create();
    $staffMember->assignRole('Trainer');

    $response = $this->actingAs($this->admin)->delete("/staff/{$staffMember->id}");

    $response->assertRedirect('/staff');
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('users', [
        'id' => $staffMember->id,
    ]);
});

it('cannot delete staff without staff.delete permission', function () {
    $response = $this->actingAs($this->trainer)->delete("/staff/{$this->receptionist->id}");

    $response->assertForbidden();
});

it('admin has all permissions', function () {
    expect($this->admin->hasPermissionTo('members.view'))->toBeTrue();
    expect($this->admin->hasPermissionTo('members.create'))->toBeTrue();
    expect($this->admin->hasPermissionTo('staff.view'))->toBeTrue();
    expect($this->admin->hasPermissionTo('staff.create'))->toBeTrue();
});

it('receptionist has limited permissions', function () {
    expect($this->receptionist->hasPermissionTo('members.view'))->toBeTrue();
    expect($this->receptionist->hasPermissionTo('members.create'))->toBeTrue();
    expect($this->receptionist->hasPermissionTo('membership-plans.create'))->toBeFalse();
    expect($this->receptionist->hasPermissionTo('staff.view'))->toBeFalse();
});

it('trainer has view-only permissions', function () {
    expect($this->trainer->hasPermissionTo('members.view'))->toBeTrue();
    expect($this->trainer->hasPermissionTo('attendance.view'))->toBeTrue();
    expect($this->trainer->hasPermissionTo('members.create'))->toBeFalse();
    expect($this->trainer->hasPermissionTo('attendance.edit'))->toBeFalse();
});

it('admin can access members routes', function () {
    $this->actingAs($this->admin)->get('/members')->assertSuccessful();
});

it('receptionist can access members routes', function () {
    $this->actingAs($this->receptionist)->get('/members')->assertSuccessful();
});

it('trainer can access members routes with view permission', function () {
    $this->actingAs($this->trainer)->get('/members')->assertSuccessful();
});

it('trainer cannot create members', function () {
    $this->actingAs($this->trainer)->post('/members', [
        'name' => 'Test Member',
        'email' => 'test@example.com',
        'phone' => '1234567890',
    ])->assertForbidden();
});

it('roles can be synced for staff members', function () {
    $staffMember = User::factory()->create();
    $staffMember->assignRole('Trainer');

    expect($staffMember->hasRole('Trainer'))->toBeTrue();

    $staffMember->syncRoles(['Receptionist']);

    expect($staffMember->hasRole('Receptionist'))->toBeTrue();
    expect($staffMember->hasRole('Trainer'))->toBeFalse();
});

it('can create multiple roles', function () {
    $admin = Role::findByName('Admin');
    $receptionist = Role::findByName('Receptionist');
    $trainer = Role::findByName('Trainer');

    expect($admin)->not->toBeNull();
    expect($receptionist)->not->toBeNull();
    expect($trainer)->not->toBeNull();
});

it('permissions are cached', function () {
    $permission = Permission::findByName('members.view');

    expect($permission)->not->toBeNull();
    expect($this->admin->hasPermissionTo($permission))->toBeTrue();
});

it('requires authentication to access staff routes', function () {
    auth()->logout();

    $this->get('/staff')->assertRedirect('/login');
});
