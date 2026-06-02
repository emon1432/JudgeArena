<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        if (Schema::hasTable('settings')) {
            $systemSettingsJson = DB::table('settings')->where('key', 'system_settings')->value('value');
            $systemSettings = $systemSettingsJson ? json_decode($systemSettingsJson, true) : [];

            config([
                'app.name' => $systemSettings['app_name'] ?? config('app.name'),
                'app.url' => $systemSettings['app_url'] ?? config('app.url'),
                'app.locale' => session('locale', $systemSettings['app_locale'] ?? config('app.locale')),
                'app.timezone' => $systemSettings['app_timezone'] ?? config('app.timezone'),
                'app.date_format' => $systemSettings['date_format'] ??  config('app.date_format'),
                'app.time_format' => $systemSettings['time_format'] ??  config('app.time_format'),
            ]);
        }
    }
}
