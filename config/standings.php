<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Standings Storage Disk
    |--------------------------------------------------------------------------
    |
    | Defines which disk is used to store compressed contest standings payloads.
    | Options: 'local' (development), 'google' (Google Drive), 's3'
    |
    */
    'disk' => env('STANDINGS_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Standings Cache Enabled
    |--------------------------------------------------------------------------
    |
    | Master toggle to enable or disable contest standings caching.
    |
    */
    'cache_enabled' => (bool) env('STANDINGS_CACHE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Compression Level
    |--------------------------------------------------------------------------
    |
    | Gzip compression level from 1 (fastest) to 9 (maximum compression).
    |
    */
    'compression_level' => (int) env('STANDINGS_COMPRESSION_LEVEL', 9),
];

