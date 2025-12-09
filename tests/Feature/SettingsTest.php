<?php

use App\Models\GymSettings;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->admin()->create();
    $this->actingAs($this->user);
});

test('guests are redirected to login', function () {
    auth()->logout();
    $response = $this->get(route('settings.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit settings page', function () {
    $response = $this->get(route('settings.index'));
    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Settings/Index')
        ->has('settings')
        ->has('timezones')
        ->has('currencies')
        ->has('paymentGateways')
    );
});

test('it displays current settings', function () {
    $settings = GymSettings::get();

    $response = $this->get(route('settings.index'));

    $response->assertInertia(fn ($page) => $page
        ->where('settings.gym_name', $settings->gym_name)
        ->where('settings.timezone', $settings->timezone)
        ->where('settings.currency', $settings->currency)
        ->where('settings.tax_rate', $settings->tax_rate)
    );
});

test('it can update gym general information', function () {
    $settings = GymSettings::get();

    $response = $this->put(route('settings.update'), [
        'gym_name' => 'New Gym Name',
        'email' => 'gym@example.com',
        'phone' => '+233244123456',
        'address' => '123 Gym Street, Accra',
        'timezone' => $settings->timezone,
        'currency' => $settings->currency,
        'tax_rate' => $settings->tax_rate,
    ]);

    $response->assertRedirect(route('settings.index'));

    $settings->refresh();
    expect($settings->gym_name)->toBe('New Gym Name');
    expect($settings->email)->toBe('gym@example.com');
    expect($settings->phone)->toBe('+233244123456');
    expect($settings->address)->toBe('123 Gym Street, Accra');
});

test('it can update timezone', function () {
    $response = $this->put(route('settings.update'), [
        'gym_name' => 'Strongsuite',
        'timezone' => 'America/New_York',
        'currency' => 'GHS',
        'tax_rate' => 15,
    ]);

    $response->assertRedirect(route('settings.index'));

    $settings = GymSettings::get();
    expect($settings->timezone)->toBe('America/New_York');
});

test('it can update currency', function () {
    $response = $this->put(route('settings.update'), [
        'gym_name' => 'Strongsuite',
        'timezone' => 'Africa/Accra',
        'currency' => 'USD',
        'tax_rate' => 15,
    ]);

    $response->assertRedirect(route('settings.index'));

    $settings = GymSettings::get();
    expect($settings->currency)->toBe('USD');
});

test('it can update tax rate', function () {
    $response = $this->put(route('settings.update'), [
        'gym_name' => 'Strongsuite',
        'timezone' => 'Africa/Accra',
        'currency' => 'GHS',
        'tax_rate' => 12.5,
    ]);

    $response->assertRedirect(route('settings.index'));

    $settings = GymSettings::get();
    expect((float) $settings->tax_rate)->toBe(12.5);
});

test('it can update payment gateways', function () {
    $response = $this->put(route('settings.update'), [
        'gym_name' => 'Strongsuite',
        'timezone' => 'Africa/Accra',
        'currency' => 'GHS',
        'tax_rate' => 15,
        'payment_gateways' => ['paystack', 'manual'],
    ]);

    $response->assertRedirect(route('settings.index'));

    $settings = GymSettings::get();
    expect($settings->payment_gateways)->toBe(['paystack', 'manual']);
});

test('it can upload gym logo', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('logo.png');

    $response = $this->put(route('settings.update'), [
        'gym_name' => 'Strongsuite',
        'timezone' => 'Africa/Accra',
        'currency' => 'GHS',
        'tax_rate' => 15,
        'logo' => $file,
    ]);

    $response->assertRedirect(route('settings.index'));

    $settings = GymSettings::get();
    expect($settings->logo)->not->toBeNull();
    Storage::disk('public')->assertExists($settings->logo);
});

test('it deletes old logo when uploading new one', function () {
    Storage::fake('public');

    $oldFile = UploadedFile::fake()->image('old-logo.png');
    $oldPath = $oldFile->store('logos', 'public');

    $settings = GymSettings::get();
    $settings->update(['logo' => $oldPath]);

    $newFile = UploadedFile::fake()->image('new-logo.png');

    $this->put(route('settings.update'), [
        'gym_name' => 'Strongsuite',
        'timezone' => 'Africa/Accra',
        'currency' => 'GHS',
        'tax_rate' => 15,
        'logo' => $newFile,
    ]);

    Storage::disk('public')->assertMissing($oldPath);
});

test('it validates required fields', function () {
    $response = $this->put(route('settings.update'), [
        'gym_name' => '',
        'timezone' => '',
        'currency' => '',
        'tax_rate' => '',
    ]);

    $response->assertSessionHasErrors(['gym_name', 'timezone', 'currency', 'tax_rate']);
});

test('it validates email format', function () {
    $response = $this->put(route('settings.update'), [
        'gym_name' => 'Strongsuite',
        'email' => 'invalid-email',
        'timezone' => 'Africa/Accra',
        'currency' => 'GHS',
        'tax_rate' => 15,
    ]);

    $response->assertSessionHasErrors(['email']);
});

test('it validates timezone is valid', function () {
    $response = $this->put(route('settings.update'), [
        'gym_name' => 'Strongsuite',
        'timezone' => 'Invalid/Timezone',
        'currency' => 'GHS',
        'tax_rate' => 15,
    ]);

    $response->assertSessionHasErrors(['timezone']);
});

test('it validates currency is valid', function () {
    $response = $this->put(route('settings.update'), [
        'gym_name' => 'Strongsuite',
        'timezone' => 'Africa/Accra',
        'currency' => 'INVALID',
        'tax_rate' => 15,
    ]);

    $response->assertSessionHasErrors(['currency']);
});

test('it validates tax rate is numeric', function () {
    $response = $this->put(route('settings.update'), [
        'gym_name' => 'Strongsuite',
        'timezone' => 'Africa/Accra',
        'currency' => 'GHS',
        'tax_rate' => 'not-a-number',
    ]);

    $response->assertSessionHasErrors(['tax_rate']);
});

test('it validates tax rate is between 0 and 100', function () {
    $response = $this->put(route('settings.update'), [
        'gym_name' => 'Strongsuite',
        'timezone' => 'Africa/Accra',
        'currency' => 'GHS',
        'tax_rate' => 150,
    ]);

    $response->assertSessionHasErrors(['tax_rate']);

    $response = $this->put(route('settings.update'), [
        'gym_name' => 'Strongsuite',
        'timezone' => 'Africa/Accra',
        'currency' => 'GHS',
        'tax_rate' => -5,
    ]);

    $response->assertSessionHasErrors(['tax_rate']);
});

test('it validates payment gateways are valid', function () {
    $response = $this->put(route('settings.update'), [
        'gym_name' => 'Strongsuite',
        'timezone' => 'Africa/Accra',
        'currency' => 'GHS',
        'tax_rate' => 15,
        'payment_gateways' => ['invalid-gateway'],
    ]);

    $response->assertSessionHasErrors(['payment_gateways.0']);
});

test('it validates logo is an image', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->create('document.pdf', 100);

    $response = $this->put(route('settings.update'), [
        'gym_name' => 'Strongsuite',
        'timezone' => 'Africa/Accra',
        'currency' => 'GHS',
        'tax_rate' => 15,
        'logo' => $file,
    ]);

    $response->assertSessionHasErrors(['logo']);
});

test('it validates logo size is under 2MB', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('logo.png')->size(3000);

    $response = $this->put(route('settings.update'), [
        'gym_name' => 'Strongsuite',
        'timezone' => 'Africa/Accra',
        'currency' => 'GHS',
        'tax_rate' => 15,
        'logo' => $file,
    ]);

    $response->assertSessionHasErrors(['logo']);
});

test('gym settings model creates singleton instance', function () {
    $settings1 = GymSettings::get();
    $settings2 = GymSettings::get();

    expect($settings1->id)->toBe($settings2->id);
    expect(GymSettings::count())->toBe(1);
});

test('gym settings model has default values', function () {
    $settings = GymSettings::get();

    expect($settings->gym_name)->toBe('Strongsuite');
    expect($settings->timezone)->toBe('Africa/Accra');
    expect($settings->currency)->toBe('GHS');
    expect((float) $settings->tax_rate)->toBe(15.0);
});
