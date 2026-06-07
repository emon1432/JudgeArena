<?php

use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\Codeforces\CodeforcesAdapter;

return [

    'codeforces' => [
        'base_url' => 'https://codeforces.com/',
        'profile_url' => 'https://codeforces.com/profile/',
        'api_base_url' => 'https://codeforces.com/api/',
        'credentials' => [
            'api_key' => env('CODEFORCES_API_KEY'),
            'api_secret' => env('CODEFORCES_API_SECRET'),
        ],
        'status' => 'Active',
        'adapter' => CodeforcesAdapter::class,
    ],

    'atcoder' => [
        'base_url' => 'https://atcoder.jp/',
        'profile_url' => 'https://atcoder.jp/users/',
        'credentials' => [
            'atcoder_username' => env('ATCODER_USERNAME'),
            'atcoder_password' => env('ATCODER_PASSWORD'),
            'atcoder_session_cookies' => env('ATCODER_SESSION_COOKIES'),
        ],
        'status' => 'Active',
        'adapter' => AtCoderAdapter::class,
    ],
];
