<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

if (!function_exists('slugify')) {
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

if (!function_exists('settings')) {
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

if (!function_exists('format_date')) {
    function format_date($date)
    {
        return \Carbon\Carbon::parse($date)->format(config('app.date_format'));
    }
}

if (!function_exists('format_time')) {
    function format_time($time)
    {
        return \Carbon\Carbon::parse($time)->format(config('app.time_format'));
    }
}

if (!function_exists('format_date_time')) {
    function format_date_time($dateTime)
    {
        return \Carbon\Carbon::parse($dateTime)->format(config('app.date_format') . ', ' . config('app.time_format'));
    }
}

if (!function_exists('format_number')) {
    function format_number($number)
    {
        $decimalSeparator = settings('system_settings', 'decimal_separator', '.');
        $thousandSeparator = settings('system_settings', 'thousand_separator', '.');
        $decimalPrecision = settings('system_settings', 'decimal_precision', 2);
        return number_format($number, $decimalPrecision, $decimalSeparator, $thousandSeparator);
    }
}
