<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\PlatformProfile;
use App\Jobs\SyncPlatformProfileJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::call(function () {
    PlatformProfile::active()
        ->chunkById(100, function ($profiles) {
            foreach ($profiles as $profile) {
                SyncPlatformProfileJob::dispatch($profile);
            }
        });
})->hourly();

Schedule::command('judgearena:sync-catalog')
    ->daily()
    ->withoutOverlapping(1430)
    ->runInBackground()
    ->name('judgearena-catalog-sync');
