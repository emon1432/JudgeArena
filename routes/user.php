<?php

use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

Route::controller(UserController::class)->group(function () {
    Route::get('/user/{username}', 'show')->name('user.show');
    Route::get('/user/{username}/platform/{platform}', 'platformProfile')->name('user.platform.show');

    Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'user'])
        ->prefix('user')
        ->as('user.')
        ->group(function () {
            Route::get('/{username}/edit', 'edit')->name('edit');
            Route::put('/{username}/update', 'update')->name('update');
        });
});
