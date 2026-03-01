<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\SyncController;
use App\Http\Controllers\User\UserProfileController;

Route::get('/user/profile/{username}', [UserProfileController::class, 'show'])->name('user.profile.show');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'user'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {

        Route::controller(UserProfileController::class)
            ->prefix('profile/{username}')
            ->name('profile.')
            ->group(function () {
                Route::get('/edit', 'edit')->name('edit');
                Route::put('/', 'update')->name('update');
                Route::post('/institute-request', 'submitInstituteRequest')->name('institute.request');
            });

        Route::controller(SyncController::class)
            ->prefix('sync')
            ->name('sync.')
            ->group(function () {
                Route::post('/', 'sync')->name('start');
                Route::get('/status', 'getSyncStatus')->name('status');
            });
    });
