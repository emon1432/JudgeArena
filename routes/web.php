<?php

use App\Http\Controllers\Admin\OthersController;
use App\Http\Controllers\Web\WebsiteController;
use Illuminate\Support\Facades\Route;

Route::controller(WebsiteController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('/platforms', 'platforms')->name('platforms.index');
    Route::get('/platforms/{slug}', 'platformDetail')->name('platforms.show');
    Route::get('/contests', 'contests')->name('contests.index');
    Route::get('/problems', 'problems')->name('problems.index');
    Route::get('/rankings', 'rankings')->name('rankings.index');
    Route::get('/community', 'community')->name('community.index');
});

Route::controller(OthersController::class)->group(function () {
    Route::post('/test-mail', 'testMail')->name('test.mail');
    Route::get('/migrate', 'migrate')->name('migration');
    Route::get('/clear', 'clear')->name('clear');
    Route::get('/composer', 'composer')->name('composer');
    Route::get('/iseed', 'iseed')->name('iseed');
});
