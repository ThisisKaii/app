<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\TaskController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

# Task and Board Routes - Final Proj
Route::get('/todo/{board}', [BoardController::class, 'show'])->name('boards.show');
Route::post('/todo', [TaskController::class, 'store'])->name('tasks.store');
Route::patch('/todo/{task}', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
#

Route::get('/test', function () {
    return view('/test');
});
require __DIR__ . '/auth.php';
