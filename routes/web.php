<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DayController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\Auth\SocialiteController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
Route::resource('tasks', TaskController::class);
});

Route::get('/calendar/{month?}/{year?}', [DayController::class, 'index'])->name('calendar.index');
Route::get('/calendar/day/{dayId}', [DayController::class, 'show'])->name('calendar.day');
Route::get('/calendar/day/{date}/schedules', [ScheduleController::class, 'getSchedulesForDay']);
Route::post('/calendar/day/{date}/schedule/store', [ScheduleController::class, 'store'])->name('schedule.store');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('tasks', TaskController::class);
});

require __DIR__.'/auth.php';
