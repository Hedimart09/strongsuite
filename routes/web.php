<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('members', \App\Http\Controllers\MemberController::class);
    Route::get('members/{member}/qr-code', [\App\Http\Controllers\MemberController::class, 'qrCode'])->name('members.qr-code');

    Route::resource('membership-plans', \App\Http\Controllers\MembershipPlanController::class);

    Route::get('members/{member}/subscriptions/create', [\App\Http\Controllers\SubscriptionController::class, 'create'])->name('subscriptions.create');
    Route::post('subscriptions', [\App\Http\Controllers\SubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::post('subscriptions/{subscription}/renew', [\App\Http\Controllers\SubscriptionController::class, 'renew'])->name('subscriptions.renew');
    Route::post('subscriptions/{subscription}/cancel', [\App\Http\Controllers\SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
});

require __DIR__.'/settings.php';
