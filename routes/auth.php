<?php

use App\Http\Controllers\Auth\AdminAuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ========================================
// ALL AUTHENTICATION ROUTES
// ========================================

// General login redirect - redirects to voter login (public access)
Route::get('/login', function () {
    return redirect()->route('auth.login');
})->name('login');

// ========================================
// ADMIN AUTHENTICATION (for Head & Officers)
// ========================================
// Note: Admin authentication uses a non-obvious path for security
// The actual roles are: Voter, Officer, Head
Route::middleware('guest')->group(function () {
    // Admin Login (used by both Head and Officers) - Non-obvious path
    Route::get('auth/admin-btech', [AdminAuthenticatedSessionController::class, 'create'])
        ->name('admin.login');

    Route::post('auth/admin-btech', [AdminAuthenticatedSessionController::class, 'store'])
        ->name('admin.login.store');

    // Admin OTP Verification
    Route::get('auth/admin-btech/otp-verify', [AdminAuthenticatedSessionController::class, 'showOtpVerification'])
        ->name('admin.otp.verify');

    Route::post('auth/admin-btech/otp-verify', [AdminAuthenticatedSessionController::class, 'verifyOtp'])
        ->name('admin.otp.verify.store');

    // Admin Password Reset (used by both Head and Officers)
    Route::get('auth/admin-btech/forgot-password', function () {
        return Inertia::render('auth/admin/forgot-password');
    })->name('admin.password.request');

    Route::post('auth/admin-btech/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('admin.password.email');

    Route::get('auth/admin-btech/reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('admin.password.reset');

    Route::post('auth/admin-btech/reset-password', [NewPasswordController::class, 'store'])
        ->name('admin.password.store');
});

// Admin Authenticated Routes (used by both Head and Officers)
Route::middleware('auth.admin')->group(function () {
    Route::get('auth/admin-btech/verify-email', EmailVerificationPromptController::class)
        ->name('admin.verification.notice');

    Route::get('auth/admin-btech/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('admin.verification.verify');

    Route::post('auth/admin-btech/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('admin.verification.send');

    Route::post('auth/admin-btech/logout', [AdminAuthenticatedSessionController::class, 'destroy'])
        ->name('admin.logout');
});

// ========================================
// UNIFIED AUTHENTICATION SYSTEM
// ========================================

// Voter Authentication (Public)
Route::prefix('auth')->name('auth.')->group(function () {
    // Voter Login & OTP
    Route::get('/login', function () {
        return Inertia::render('auth/voter/login');
    })->name('login');

    Route::post('/login', function () {
        // Handle voter login with OTP
    })->name('login.submit');

    Route::get('/otp-verify', function () {
        return Inertia::render('auth/voter/otp-verify');
    })->name('otp.verify');

    Route::post('/otp-verify', function () {
        // Handle OTP verification
    })->name('otp.verify.submit');

    // Two-Factor Authentication
    Route::get('/two-factor-challenge', function () {
        return Inertia::render('auth/voter/two-factor-challenge');
    })->name('two-factor.challenge');

    Route::post('/two-factor-challenge', function () {
        // Handle two-factor authentication
    })->name('two-factor.verify');
});

// ========================================
// ELECTORAL HEAD AUTHENTICATION
// ========================================
Route::prefix('head')->name('head.')->group(function () {
    // Head login (redirects to admin authentication)
    Route::get('/login', function () {
        return redirect()->route('admin.login');
    })->name('login');
});

// ========================================
// ELECTORAL OFFICER AUTHENTICATION
// ========================================
Route::prefix('officers')->name('officers.')->group(function () {
    // Officer login (redirects to admin authentication)
    Route::get('/login', function () {
        return redirect()->route('admin.login');
    })->name('login');
});
