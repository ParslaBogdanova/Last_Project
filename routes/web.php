<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DayController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\Auth\SocialiteController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', function () {
    return view('welcome');
});

// Dashboard (Requires Authentication)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Task Routes (Requires Authentication)
Route::middleware('auth')->group(function () {
    Route::resource('tasks', TaskController::class);
    Route::patch('/tasks/{task}/update-completed', [TaskController::class, 'updateCompleted'])->name('tasks.update-completed');
});

Route::middleware('auth')->group(function () {
Route::get('/calendar/{month?}/{year?}', [CalendarController::class, 'index'])->name('calendar.index');
Route::post('/calendar/{month?}/{year?}', [CalendarController::class, 'createSchedule'])->name('calendar.createSchedule');
});



Route::middleware('auth')->group(function () {
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/chat/{userId}', [MessageController::class, 'getChatHistory']);
    Route::post('/messages/send', [MessageController::class, 'sendMessage']);
});



// Profile Management (Requires Authentication)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Socialite Routes (Optional - If you're using Socialite for third-party login)
Route::middleware('guest')->group(function () {
    Route::get('/auth/redirect', [SocialiteController::class, 'redirectToProvider'])->name('auth.redirect');
    Route::get('/auth/callback', [SocialiteController::class, 'handleProviderCallback'])->name('auth.callback');
});

// Include Authentication Routes
require __DIR__.'/auth.php';
