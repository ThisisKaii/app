<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\BudgetCategoryController;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Auth\ForgotPasswordController;

// Password Reset Routes
Route::middleware('guest')->group(function () {
    // Show forgot password form
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])
        ->name('password.request');

    // Send OTP
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOTP'])
        ->name('password.email');

    // Show OTP verification form
    Route::get('/verify-otp', [ForgotPasswordController::class, 'showVerifyOTPForm'])
        ->name('password.verify-otp');

    // Verify OTP
    Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOTP'])
        ->name('password.verify');

    // Show reset password form
    Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])
        ->name('password.reset');

    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])
        ->name('password.update');

    // Resend OTP
    Route::post('/resend-otp', [ForgotPasswordController::class, 'resendOTP'])
        ->name('password.resend');
});
Route::get('/test-email', function () {
    try {
        Mail::raw('This is a test email from Laravel', function ($message) {
            $message->to('joshuaasingua499@gmail.com')
                ->subject('Test Email From Joshua Asingua');
        });

        return 'Email sent successfully!';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/todobido', function () {
    return view('todobido');
})->name('todobido');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\dashboardController::class, 'showBoard'])->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

# Task and Board Routes - Final Proj
Route::middleware('auth')->group(function () {
    Route::get('/todobido/{board}', [BoardController::class, 'show'])->name('boards.show');
    Route::post('/todobido', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/todobido/{task}', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    Route::get('/todobido', function () {
        return view('/todobido');
    });
    Route::patch('/todobido/budget-category/{category}', [BudgetCategoryController::class, 'updateStatus'])
        ->name('budget-categories.updateStatus');
});
#

Route::middleware('auth')->group(function () {
    // Board management
    Route::post('/boards', [BoardController::class, 'store'])->name('boards.store');
    Route::patch('/boards/{board}', [BoardController::class, 'update'])->name('boards.update');
    Route::delete('/boards/{board}', [BoardController::class, 'destroy'])->name('boards.destroy');

    // Task management
    Route::patch('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
});

Route::get('/test', function () {
    return view('/test');
});
require __DIR__ . '/auth.php';
