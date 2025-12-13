<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('reports', [\App\Http\Controllers\ReportsController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('reports');

Route::get('gym-settings', [\App\Http\Controllers\SettingsController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('settings.index');

Route::match(['post', 'put', 'patch'], 'gym-settings', [\App\Http\Controllers\SettingsController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('settings.update');

// Member Portal routes
Route::prefix('member')->name('member.')->group(function () {
    Route::get('login', [\App\Http\Controllers\Member\MemberAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [\App\Http\Controllers\Member\MemberAuthController::class, 'login']);
    Route::post('logout', [\App\Http\Controllers\Member\MemberAuthController::class, 'logout'])->name('logout');

    // Public PIN viewing route (no auth required)
    Route::get('pin/{token}', [\App\Http\Controllers\Member\MemberPinController::class, 'show'])->name('pin.view');

    Route::middleware('member.auth')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Member\MemberDashboardController::class, 'index'])->name('dashboard');
        Route::get('check-in', [\App\Http\Controllers\Member\MemberCheckInController::class, 'show'])->name('check-in');
        Route::post('check-in', [\App\Http\Controllers\Member\MemberCheckInController::class, 'checkIn'])->name('check-in.store');
    });
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Member routes
    Route::middleware('permission:members.create')->group(function () {
        Route::get('members/create', [\App\Http\Controllers\MemberController::class, 'create'])->name('members.create');
        Route::post('members', [\App\Http\Controllers\MemberController::class, 'store'])->name('members.store');
    });
    Route::middleware('permission:members.edit')->group(function () {
        Route::get('members/{member}/edit', [\App\Http\Controllers\MemberController::class, 'edit'])->name('members.edit');
        Route::match(['post', 'put', 'patch'], 'members/{member}', [\App\Http\Controllers\MemberController::class, 'update'])->name('members.update');
    });
    Route::middleware('permission:members.view')->group(function () {
        Route::get('members', [\App\Http\Controllers\MemberController::class, 'index'])->name('members.index');
        Route::get('members/{member}', [\App\Http\Controllers\MemberController::class, 'show'])->name('members.show');
        Route::get('members/{member}/qr-code', [\App\Http\Controllers\MemberController::class, 'qrCode'])->name('members.qr-code');
    });
    Route::delete('members/{member}', [\App\Http\Controllers\MemberController::class, 'destroy'])->middleware('permission:members.delete')->name('members.destroy');

    // Membership Plan routes
    Route::middleware('permission:membership-plans.create')->group(function () {
        Route::get('membership-plans/create', [\App\Http\Controllers\MembershipPlanController::class, 'create'])->name('membership-plans.create');
        Route::post('membership-plans', [\App\Http\Controllers\MembershipPlanController::class, 'store'])->name('membership-plans.store');
    });
    Route::middleware('permission:membership-plans.edit')->group(function () {
        Route::get('membership-plans/{membershipPlan}/edit', [\App\Http\Controllers\MembershipPlanController::class, 'edit'])->name('membership-plans.edit');
        Route::match(['put', 'patch'], 'membership-plans/{membershipPlan}', [\App\Http\Controllers\MembershipPlanController::class, 'update'])->name('membership-plans.update');
    });
    Route::middleware('permission:membership-plans.view')->group(function () {
        Route::get('membership-plans', [\App\Http\Controllers\MembershipPlanController::class, 'index'])->name('membership-plans.index');
        Route::get('membership-plans/{membershipPlan}', [\App\Http\Controllers\MembershipPlanController::class, 'show'])->name('membership-plans.show');
    });
    Route::delete('membership-plans/{membershipPlan}', [\App\Http\Controllers\MembershipPlanController::class, 'destroy'])->middleware('permission:membership-plans.delete')->name('membership-plans.destroy');

    // Subscription routes
    Route::middleware('permission:subscriptions.create')->group(function () {
        Route::get('members/{member}/subscriptions/create', [\App\Http\Controllers\SubscriptionController::class, 'create'])->name('subscriptions.create');
        Route::post('subscriptions', [\App\Http\Controllers\SubscriptionController::class, 'store'])->name('subscriptions.store');
    });
    Route::middleware('permission:subscriptions.edit')->group(function () {
        Route::post('subscriptions/{subscription}/renew', [\App\Http\Controllers\SubscriptionController::class, 'renew'])->name('subscriptions.renew');
        Route::post('subscriptions/{subscription}/cancel', [\App\Http\Controllers\SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    });

    // Attendance routes
    Route::middleware('permission:attendance.view')->group(function () {
        Route::get('attendance', [\App\Http\Controllers\AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('attendance/scan', [\App\Http\Controllers\AttendanceController::class, 'scan'])->name('attendance.scan');
        Route::get('attendance/manual', [\App\Http\Controllers\AttendanceController::class, 'manual'])->name('attendance.manual');
    });
    Route::middleware('permission:attendance.create')->group(function () {
        Route::post('attendance/check-in/qr', [\App\Http\Controllers\AttendanceController::class, 'checkInByQr'])->name('attendance.check-in.qr');
        Route::post('attendance/check-in/manual', [\App\Http\Controllers\AttendanceController::class, 'checkInManual'])->name('attendance.check-in.manual');
        Route::post('attendance/{attendance}/check-out', [\App\Http\Controllers\AttendanceController::class, 'checkOut'])->name('attendance.check-out');
    });

    // Invoice routes
    Route::middleware('permission:invoices.view')->group(function () {
        Route::get('invoices', [\App\Http\Controllers\InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('invoices/{invoice}/pdf', [\App\Http\Controllers\InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    });
    Route::middleware('permission:invoices.edit')->group(function () {
        Route::post('invoices/{invoice}/mark-paid', [\App\Http\Controllers\InvoiceController::class, 'markAsPaid'])->name('invoices.mark-paid');
    });

    // Staff routes
    Route::middleware('permission:staff.view')->group(function () {
        Route::get('staff', [\App\Http\Controllers\StaffController::class, 'index'])->name('staff.index');
        Route::get('staff/{staff}', [\App\Http\Controllers\StaffController::class, 'show'])->name('staff.show');
    });
    Route::middleware('permission:staff.create')->group(function () {
        Route::get('staff/create', [\App\Http\Controllers\StaffController::class, 'create'])->name('staff.create');
        Route::post('staff', [\App\Http\Controllers\StaffController::class, 'store'])->name('staff.store');
    });
    Route::middleware('permission:staff.edit')->group(function () {
        Route::get('staff/{staff}/edit', [\App\Http\Controllers\StaffController::class, 'edit'])->name('staff.edit');
        Route::match(['put', 'patch'], 'staff/{staff}', [\App\Http\Controllers\StaffController::class, 'update'])->name('staff.update');
    });
    Route::delete('staff/{staff}', [\App\Http\Controllers\StaffController::class, 'destroy'])->middleware('permission:staff.delete')->name('staff.destroy');
});

require __DIR__.'/settings.php';
