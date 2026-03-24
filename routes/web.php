<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\GoalMilestoneController;
use App\Http\Controllers\HabitController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TodayController;
use App\Http\Controllers\WeightLossController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/today')->name('home');

Route::get('/login', [AuthController::class, 'show'])->name('login.show');
Route::post('/login', [AuthController::class, 'store'])->name('login.store');
Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

Route::middleware('app.auth')->group(function () {
    Route::get('/today', [TodayController::class, 'index'])->name('today.index');
    Route::post('/today/{habit}/logs', [TodayController::class, 'store'])->name('today.logs.store');
    Route::post('/today/weight-logs', [TodayController::class, 'storeWeight'])->name('today.weight.store');
    Route::patch('/today/milestones/{milestone}/toggle', [TodayController::class, 'toggleMilestone'])->name('today.milestones.toggle');

    Route::get('/habits', [HabitController::class, 'index'])->name('habits.index');
    Route::post('/habits', [HabitController::class, 'store'])->name('habits.store');
    Route::patch('/habits/reorder', [HabitController::class, 'reorder'])->name('habits.reorder');
    Route::put('/habits/{habit}', [HabitController::class, 'update'])->name('habits.update');
    Route::delete('/habits/{habit}', [HabitController::class, 'destroy'])->name('habits.destroy');

    Route::get('/goals', [GoalController::class, 'index'])->name('goals.index');
    Route::post('/goals', [GoalController::class, 'store'])->name('goals.store');
    Route::get('/goals/{goal}', [GoalController::class, 'show'])->name('goals.show');
    Route::post('/goals/{goal}/milestones', [GoalMilestoneController::class, 'store'])->name('goals.milestones.store');
    Route::patch('/goals/{goal}/milestones/reorder', [GoalMilestoneController::class, 'reorder'])->name('goals.milestones.reorder');
    Route::patch('/goals/{goal}/milestones/{milestone}', [GoalMilestoneController::class, 'update'])->name('goals.milestones.update');

    Route::get('/weight-loss', [WeightLossController::class, 'index'])->name('weight-loss.index');
    Route::post('/weight-loss', [WeightLossController::class, 'store'])->name('weight-loss.store');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/timeline', [SettingsController::class, 'updateTimeline'])->name('settings.timeline.update');
    Route::post('/settings/login', [SettingsController::class, 'updateLogin'])->name('settings.login.update');
});
