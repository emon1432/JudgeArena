<?php

use App\Http\Controllers\Admin\OthersController;
use App\Http\Controllers\Web\LeaderboardController;
use App\Http\Controllers\Web\SeoController;
use App\Http\Controllers\Web\WebsiteController;
use Illuminate\Support\Facades\Route;




Route::controller(WebsiteController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/contact-us', 'contactUs')->name('contact.us');
    Route::post('/contact-us', 'submitContact')->name('contact.submit');
    Route::get('/problems', 'problems')->name('problems');
    Route::get('/contests', 'contests')->name('contests');
    Route::get('/community', 'community')->name('community');
});

Route::controller(LeaderboardController::class)->group(function () {
    Route::get('/leaderboard', 'index')->name('leaderboard');
});

Route::controller(OthersController::class)->group(function () {
    Route::middleware('guest')->get('/admin/login', 'login')->name('admin.login');
    Route::get('/ajax/select2-options', 'select2Options')->name('select2.options');
    Route::get('/sitemap.xml', 'sitemap')->name('sitemap');
    Route::post('/test-mail', 'testMail')->name('test.mail');
    Route::get('/migrate', 'migrate')->name('migration');
    Route::get('/clear', 'clear')->name('clear');
    Route::get('/composer', 'composer')->name('composer');
    Route::get('/iseed', 'iseed')->name('iseed');
});
