<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\ContestController;
use App\Http\Controllers\Admin\ApplicationLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PlatformController;
use App\Http\Controllers\Admin\ProblemController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SyncMonitorController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'admin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
    });
    Route::resource('platforms', PlatformController::class);
    Route::resource('all-problems', ProblemController::class)->only(['index', 'show']);
    Route::resource('all-contests', ContestController::class)->only(['index', 'show']);
    Route::resource('users', UserController::class)->only(['index', 'show']);
    Route::resource('contact-messages', ContactMessageController::class)->only(['index', 'show']);
    Route::resource('logs', ApplicationLogController::class)->only(['index', 'show']);
    Route::get('sync-monitor', [SyncMonitorController::class, 'index'])->name('sync-monitor.index');
    Route::post('sync-monitor/{syncState}/retry', [SyncMonitorController::class, 'retry'])->name('sync-monitor.retry');
    Route::resource('admins', AdminController::class);
    Route::resource('settings', SettingController::class)->only('index', 'update');
});
