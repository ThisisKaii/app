<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes (Cleaned)
|--------------------------------------------------------------------------
| IMPORTANT:
| All default Breeze/Fortify password-reset routes are REMOVED so that
| your custom OTP-based password reset system works correctly.
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    // Register
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    // Login
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // ❌ Removed: forgot password (your OTP routes replace all of this)
    // Route::get('forgot-password', ... );
    // Route::post('forgot-password', ... );
    // Route::get('reset-password/{token}', ... );
    // Route::put('password', ... );
});

Route::middleware('auth')->group(function () {

    // Email verification prompt
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    // Email verification handler
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    // Resend verification email
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Confirm password
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    // ❌ Removed: default PUT password.update (was breaking your reset route)
    // Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    // Logout
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
