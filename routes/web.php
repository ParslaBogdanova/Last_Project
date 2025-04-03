<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ZoomMeetingController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReminderZoomMeetingController;
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

Route::middleware('auth')->group(function () {
    Route::resource('tasks', TaskController::class);
    Route::patch('/tasks/{task}/update-completed', [TaskController::class, 'updateCompleted'])->name('tasks.update-completed');
    Route::patch('/tasks/resetWeeklyData', [TaskController::class, 'resetWeeklyData'])->name('tasks.resetWeeklyData');
    Route::get('/tasks/notifications', [NotificationController::class, 'index'])->name('notifications.index');
});


Route::middleware('auth')->group(function () {
    Route::get('/calendar/{month?}/{year?}', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/{month}/{year}/{date}', [CalendarController::class, 'show'])->name('calendar.show');

    Route::get('/calendar/{month}/{year}/{date}/schedules/create', [ScheduleController::class, 'create'])
        ->name('schedules.create');
    Route::post('/calendar/{month}/{year}/{date}/schedules', [ScheduleController::class, 'store'])
        ->name('schedules.store');
    Route::get('/calendar/{month}/{year}/{date}/schedules/edit', [ScheduleController::class, 'edit'])
        ->name('schedules.edit');
    Route::put('/calendar/{month}/{year}/{date}/schedules', [ScheduleController::class, 'update'])
        ->name('schedules.update');
    Route::delete('/calendar/{month}/{year}/{date}/schedules/{schedule_id}', [ScheduleController::class, 'destroy'])
    ->name('schedules.destroy');

    Route::post('/calendar/{month}/{year}/{date}/block', [CalendarController::class, 'blockDay'])->name('calendar.blockDay');
Route::delete('/calendar/{month}/{year}/{date}/unblock', [CalendarController::class, 'unblock'])->name('calendar.unblock');

Route::get('/calendar/{month}/{year}/{date}/zoom_meetings/create', [ZoomMeetingController::class, 'create'])
->name('zoom_meetings.create');
Route::post('/calendar/{month}/{year}/{date}/zoom_meetings', [ZoomMeetingController::class, 'store'])
->name('zoom_meetings.store');
Route::get('/calendar/{month}/{year}/{date}/zoom_meetings/{zoom_meetings_id}/edit', [ZoomMeetingController::class, 'edit'])
->name('zoom_meetings.edit');
Route::put('/calendar/{month}/{year}/{date}/zoom_meetings', [ZoomMeetingController::class, 'update'])
->name('zoom_meetings.update');
Route::delete('/calendar/{month}/{year}/{date}/zoom_meetings/{zoom_meetings_id}', [ZoomMeetingController::class, 'destroy'])
->name('zoom_meetings.destroy');
});


Route::middleware('auth')->group(function () {
    Route::resource('messages', MessageController::class);
    Route::get('/messages/{user_id}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{user_id}', [MessageController::class, 'store']);
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
