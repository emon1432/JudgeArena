<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

if (! function_exists('slugify')) {
    function slugify(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $transliterated = iconv('utf-8', 'us-ascii//TRANSLIT//IGNORE', $text);
        if ($transliterated !== false) {
            $text = $transliterated;
        }
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = mb_strtolower($text);
        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}

if (! function_exists('settings')) {
    function settings(string $key, $field = null, $default = null)
    {
        $setting = Cache::remember("settings.{$key}", 60, function () use ($key) {
            return DB::table('settings')->where('key', $key)->value('value');
        });

        $decoded = json_decode($setting, true);

        if ($field) {
            return $decoded[$field] ?? $default;
        }

        return $decoded ?? $default;
    }
}

if (! function_exists('display_timezone')) {
    /**
     * Resolve the active display timezone.
     * Hierarchy:
     * 1. If explicit $user passed or logged in user with custom timezone -> user's timezone
     * 2. If request is within Admin panel (route is admin.* or url starts with admin) -> system settings timezone (default Asia/Dhaka)
     * 3. Fallback for general web / guest users -> UTC
     */
    function display_timezone(?\App\Models\User $user = null): string
    {
        $resolvedUser = $user ?? auth()->user();
        if ($resolvedUser && ! empty($resolvedUser->timezone)) {
            return (string) $resolvedUser->timezone;
        }

        if (request()?->is('admin*') || str_starts_with(request()?->route()?->getName() ?? '', 'admin.')) {
            return (string) settings('system_settings', 'app_timezone', 'Asia/Dhaka');
        }

        return 'UTC';
    }
}

if (! function_exists('to_display_timezone')) {
    /**
     * Convert any date/time into a Carbon instance in the display timezone.
     */
    function to_display_timezone(mixed $dateTime, ?string $timezone = null): ?Carbon
    {
        if ($dateTime === null || $dateTime === '') {
            return null;
        }

        $targetTimezone = $timezone ?: display_timezone();

        try {
            if ($dateTime instanceof Carbon) {
                return $dateTime->copy()->setTimezone($targetTimezone);
            }

            if ($dateTime instanceof \DateTimeInterface) {
                return Carbon::instance($dateTime)->setTimezone($targetTimezone);
            }

            if (is_numeric($dateTime)) {
                return Carbon::createFromTimestamp((int) $dateTime, 'UTC')->setTimezone($targetTimezone);
            }

            return Carbon::parse((string) $dateTime, 'UTC')->setTimezone($targetTimezone);
        } catch (\Throwable) {
            return null;
        }
    }
}

if (! function_exists('format_date_time')) {
    function format_date_time(mixed $dateTime, ?string $timezone = null, ?string $format = null): string
    {
        $carbon = to_display_timezone($dateTime, $timezone);
        if ($carbon === null) {
            return '-';
        }

        if ($format) {
            return $carbon->format($format);
        }

        $dateFormat = (string) (config('app.date_format') ?: settings('system_settings', 'date_format', 'd M, Y'));
        $timeFormat = (string) (config('app.time_format') ?: settings('system_settings', 'time_format', 'h:i A'));

        return $carbon->format("{$dateFormat} {$timeFormat}");
    }
}

if (! function_exists('format_date')) {
    function format_date(mixed $date, ?string $timezone = null, ?string $format = null): string
    {
        $carbon = to_display_timezone($date, $timezone);
        if ($carbon === null) {
            return '-';
        }

        $dateFormat = $format ?: (string) (config('app.date_format') ?: settings('system_settings', 'date_format', 'd M, Y'));

        return $carbon->format($dateFormat);
    }
}

if (! function_exists('format_time')) {
    function format_time(mixed $time, ?string $timezone = null, ?string $format = null): string
    {
        $carbon = to_display_timezone($time, $timezone);
        if ($carbon === null) {
            return '-';
        }

        $timeFormat = $format ?: (string) (config('app.time_format') ?: settings('system_settings', 'time_format', 'h:i A'));

        return $carbon->format($timeFormat);
    }
}

if (! function_exists('format_number')) {
    function format_number($number)
    {
        $decimalSeparator = settings('system_settings', 'decimal_separator', '.');
        $thousandSeparator = settings('system_settings', 'thousand_separator', '.');
        $decimalPrecision = settings('system_settings', 'decimal_precision', 2);

        return number_format($number, $decimalPrecision, $decimalSeparator, $thousandSeparator);
    }
}
