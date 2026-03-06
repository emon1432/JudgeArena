<?php

return [
    // Sync cooldown in minutes (default: 120 minutes = 2 hours)
    'sync_cooldown_minutes' => env('SYNC_COOLDOWN_MINUTES', 120),

    // FlareSolverr URL for bypassing Cloudflare (used by SPOJ)
    // Run: docker run -d -p 8191:8191 --name=flaresolverr ghcr.io/flaresolverr/flaresolverr:latest
    'flaresolverr_url' => env('FLARESOLVERR_URL', null),

    'hackerearth' => [
        'supports_rating' => true,
        'supports_solved' => true,
        'supports_submissions' => true,
        'sync_cost' => 'high',
        'playwright' => [
            'enabled' => env('HACKEREARTH_PLAYWRIGHT_ENABLED', true),
            'timeout_ms' => env('HACKEREARTH_PLAYWRIGHT_TIMEOUT_MS', 25000),
            'browser' => env('HACKEREARTH_PLAYWRIGHT_BROWSER', 'chromium'),
        ],
        'note' => 'Uses Playwright fallback for Next.js-rendered profile metrics (problem_solved).',
    ],

    'codeforces' => [
        'supports_rating' => true,
        'supports_solved' => true,
        'supports_submissions' => true,
        'supports_rating_graph' => true,
        'supports_contest_history' => true,
        'supports_problem_tags' => true,
        'sync_cost' => 'medium',
        'features' => [
            'max_rating' => true,
            'rank_tracking' => true,
            'contest_participation' => true,
            'problem_tags' => true,
            'submission_details' => true,
        ],
        'note' => 'Full functionality: rating, max rating, contest history, problem tags, submission details',
    ],

    'leetcode' => [
        'supports_rating' => true,
        'supports_solved' => true,
        'supports_submissions' => false,
        'supports_contest_history' => true,
        'supports_badges' => true,
        'supports_calendar' => true,
        'sync_cost' => 'low',
        'features' => [
            'difficulty_breakdown' => true,
            'contest_rating' => true,
            'contest_ranking' => true,
            'badges' => true,
            'recent_submissions' => true,
            'submission_calendar' => true,
            'streak_tracking' => true,
        ],
        'note' => 'GraphQL API: profile, difficulty breakdown, contest rating/history, badges, calendar, recent 20 submissions',
    ],

    'atcoder' => [
        'api_url' => env('ATCODER_API_URL', 'https://kenkoooo.com/atcoder/atcoder-api'),
        'supports_rating' => true,
        'supports_solved' => true,
        'supports_submissions' => false,
        'supports_rating_graph' => true,
        'supports_contest_history' => true,
        'sync_cost' => 'medium',
        'features' => [
            'highest_rating' => true,
            'rank_tracking' => true,
            'contest_participation' => true,
            'editorial_links' => true,
            'performance_rating' => true,
            'submission_details' => false,
        ],
        'note' => 'Uses only Kenkoooo v3/user/ac_rank and v2/user_info for solved count. Submission syncing is disabled.',
        'known_issues' => [
            'kenkoooo_api_403' => 'Kenkoooo API may return 403 from some networks/environments, resulting in solved count fallback to 0.',
        ],
        'catalog' => [
            // Prefer Kenkoooo resources API (with browser fallback) before HTML scraping.
            'resource_mode_enabled' => env('ATCODER_CATALOG_RESOURCE_MODE_ENABLED', true),
            // Archive pages to scan from newest to oldest for contest upsert.
            'archive_pages' => env('ATCODER_CATALOG_ARCHIVE_PAGES', 2),
            // Number of most recent contests for which task lists are fetched.
            'max_contests_for_problems' => env('ATCODER_CATALOG_MAX_CONTESTS_FOR_PROBLEMS', 120),
            // Sleep between task-page requests to avoid aggressive request bursts.
            'task_delay_ms' => env('ATCODER_CATALOG_TASK_DELAY_MS', 120),
        ],
    ],

    'codechef' => [
        'supports_rating' => true,
        'supports_solved' => true,
        'supports_submissions' => false,
        'supports_rating_graph' => true,
        'sync_cost' => 'low',
        'features' => [
            'max_rating' => true,
            'stars' => true,
            'rank_tracking' => true,
            'contest_categories' => true,
            'fully_partially_solved' => true,
            'badges' => true,
            'problem_api' => true,
        ],
        'note' => 'Profile scraping with rating graph by contest category. Submissions require OAuth API (not implemented).',
        'contest_types' => [
            'long' => 'Long Challenge',
            'cookoff' => 'Cook-off',
            'lunchtime' => 'Lunchtime',
            'starters' => 'Starters',
        ],
    ],

    'spoj' => [
        'supports_rating' => false,
        'supports_solved' => true,
        'supports_submissions' => true,
        'sync_cost' => 'medium',
        'features' => [
            'rank_tracking' => true,
            'problem_details' => true, // tags and author from problem page
            'cloudflare_handling' => true, // Automatic retry with delays
        ],
        'note' => 'Profile scraping with rank. Submissions via paginated status pages. Handles Cloudflare challenge with retry logic.',
        'known_issues' => [
            'cloudflare_delay' => 'SPOJ uses Cloudflare challenge which takes 3-10 seconds. Implementation handles this with automatic retries.',
        ],
    ],

    'hackerrank' => [
        'supports_rating' => false,
        'supports_solved' => true,
        'supports_submissions' => true,
        'supports_rating_graph' => true,
        'sync_cost' => 'medium',
        'features' => [
            'rating_graph' => true,
            'recent_submissions' => true,
            'problem_details' => true, // tags/editorial/author
        ],
        'note' => 'Uses HackerRank REST endpoints for rating graph and recent challenges.',
    ],

    'uva' => [
        'supports_rating' => false,
        'supports_solved' => true,
        'supports_submissions' => true,
        'sync_cost' => 'low',
        'features' => [
            'rank_tracking' => true,
        ],
        'note' => 'Uses UHunt API for profile and submissions. Fast and reliable.',
    ],

    'timus' => [
        'supports_rating' => false,
        'supports_solved' => true,
        'supports_submissions' => true,
        'sync_cost' => 'low',
        'features' => [
            'rank_tracking' => true,
        ],
        'note' => 'Profile and submissions scraping. Simple and efficient.',
    ],
];
