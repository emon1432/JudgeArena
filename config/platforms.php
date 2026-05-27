<?php

return [

    'codeforces' => [
        'base_url' => 'https://codeforces.com/',
        'api_base_url' => 'https://codeforces.com/api/',
        'credentials' => [
            'api_key' => env('CODEFORCES_API_KEY'),
            'api_secret' => env('CODEFORCES_API_SECRET'),
        ],
        'status' => 'active',
    ],

    'atcoder' => [
        'base_url' => 'https://atcoder.jp/',
        'credentials' => [
            'atcoder_username' => env('ATCODER_USERNAME'),
            'atcoder_password' => env('ATCODER_PASSWORD'),
            'atcoder_session_cookies' => env('ATCODER_SESSION_COOKIES'),
        ],
        'status' => 'active',
    ],
];
