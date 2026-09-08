<?php

namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class MailConfigServiceProvider extends ServiceProvider
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
            $mailSettings = DB::table('settings')->where('key', 'mail_settings')->value('value');
            if ($mailSettings) {
                $settings = json_decode($mailSettings, true);

                Config::set('mail.default', $settings['mail_driver'] ?? 'smtp');
                Config::set('mail.mailers.smtp.transport', $settings['mail_driver'] ?? 'smtp');
                Config::set('mail.mailers.smtp.host', $settings['mail_host'] ?? 'localhost');
                Config::set('mail.mailers.smtp.port', $settings['mail_port'] ?? 587);
                Config::set('mail.mailers.smtp.username', $settings['mail_username'] ?? null);
                Config::set('mail.mailers.smtp.password', $settings['mail_password'] ?? null);
                Config::set('mail.mailers.smtp.encryption', $settings['mail_encryption'] ?? 'tls');
                Config::set('mail.from.address', $settings['mail_from_address'] ?? 'example@example.com');
                Config::set('mail.from.name', $settings['mail_from_name'] ?? 'Example');
            }
        }
    }
}
