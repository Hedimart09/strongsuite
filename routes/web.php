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
});

require __DIR__.'/settings.php';
