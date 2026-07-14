<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PlatformSyncJobsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('platform_sync_jobs')->delete();
        
        \DB::table('platform_sync_jobs')->insert(array (
            0 => 
            array (
                'id' => 1,
                'platform_id' => 1,
                'entity' => 'contest',
                'enabled' => 1,
                'priority' => 100,
                'interval_minutes' => 15,
                'last_started_at' => '2026-07-14 08:16:33',
                'last_finished_at' => '2026-07-14 08:16:41',
                'last_failed_at' => '2026-07-13 13:50:41',
                'last_success_at' => '2026-07-14 08:16:41',
                'last_error' => NULL,
                'metadata' => '{"exception":"TypeError","last_stats":{"checked":2129,"fetched":2129,"created":0,"updated":0,"failed":0,"skipped":2129,"metadata":{"platform":"codeforces","entity":"contest"}}}',
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-14 08:16:41',
            ),
            1 => 
            array (
                'id' => 2,
                'platform_id' => 1,
                'entity' => 'problem',
                'enabled' => 1,
                'priority' => 90,
                'interval_minutes' => 30,
                'last_started_at' => '2026-07-14 08:17:08',
                'last_finished_at' => '2026-07-14 08:18:29',
                'last_failed_at' => '2026-07-13 16:05:44',
                'last_success_at' => '2026-07-14 08:18:29',
                'last_error' => NULL,
                'metadata' => '{"exception":"TypeError","last_stats":{"checked":2129,"fetched":7,"created":7,"updated":0,"failed":25,"skipped":2103,"metadata":{"platform":"codeforces","entity":"problem"}}}',
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-14 08:18:29',
            ),
            2 => 
            array (
                'id' => 3,
                'platform_id' => 1,
                'entity' => 'user',
                'enabled' => 1,
                'priority' => 80,
                'interval_minutes' => 60,
                'last_started_at' => '2026-07-14 08:19:08',
                'last_finished_at' => '2026-07-14 08:19:08',
                'last_failed_at' => '2026-07-13 20:02:11',
                'last_success_at' => '2026-07-14 08:19:08',
                'last_error' => NULL,
                'metadata' => '{"exception":"TypeError","last_stats":{"checked":2,"fetched":0,"created":0,"updated":0,"failed":0,"skipped":2,"metadata":{"platform":"codeforces","entity":"user"}}}',
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-14 08:19:08',
            ),
            3 => 
            array (
                'id' => 4,
                'platform_id' => 1,
                'entity' => 'submission',
                'enabled' => 0,
                'priority' => 95,
                'interval_minutes' => 15,
                'last_started_at' => NULL,
                'last_finished_at' => NULL,
                'last_failed_at' => NULL,
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-11 17:44:29',
            ),
            4 => 
            array (
                'id' => 5,
                'platform_id' => 1,
                'entity' => 'standing',
                'enabled' => 1,
                'priority' => 90,
                'interval_minutes' => 30,
                'last_started_at' => '2026-07-14 08:18:29',
                'last_finished_at' => '2026-07-14 08:18:29',
                'last_failed_at' => '2026-07-14 08:18:29',
                'last_success_at' => NULL,
            'last_error' => 'Call to undefined method App\\Platforms\\Codeforces\\CodeforcesAdapter::standingImporter()',
                'metadata' => '{"exception":"Error"}',
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-14 08:18:29',
            ),
            5 => 
            array (
                'id' => 6,
                'platform_id' => 1,
                'entity' => 'rating_change',
                'enabled' => 0,
                'priority' => 90,
                'interval_minutes' => 30,
                'last_started_at' => NULL,
                'last_finished_at' => NULL,
                'last_failed_at' => NULL,
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-11 17:44:29',
            ),
            6 => 
            array (
                'id' => 7,
                'platform_id' => 1,
                'entity' => 'user_rating_history',
                'enabled' => 1,
                'priority' => 90,
                'interval_minutes' => 30,
                'last_started_at' => '2026-07-14 08:18:29',
                'last_finished_at' => '2026-07-14 08:18:35',
                'last_failed_at' => NULL,
                'last_success_at' => '2026-07-14 08:18:35',
                'last_error' => NULL,
                'metadata' => '{"last_stats":{"checked":2,"fetched":311,"created":311,"updated":0,"failed":0,"skipped":0,"metadata":{"platform":"codeforces","entity":"user_rating_history"}}}',
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-14 08:18:35',
            ),
            7 => 
            array (
                'id' => 8,
                'platform_id' => 2,
                'entity' => 'contest',
                'enabled' => 1,
                'priority' => 100,
                'interval_minutes' => 15,
                'last_started_at' => '2026-07-14 08:16:41',
                'last_finished_at' => '2026-07-14 08:17:08',
                'last_failed_at' => '2026-07-14 08:17:08',
                'last_success_at' => NULL,
            'last_error' => 'cURL error 28: Resolving timed out after 10000 milliseconds (see https://curl.haxx.se/libcurl/c/libcurl-errors.html) for https://atcoder.jp/contests/archive?lang=ja&page=7',
                'metadata' => '{"exception":"Illuminate\\\\Http\\\\Client\\\\ConnectionException"}',
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-14 08:17:08',
            ),
            8 => 
            array (
                'id' => 9,
                'platform_id' => 2,
                'entity' => 'problem',
                'enabled' => 1,
                'priority' => 90,
                'interval_minutes' => 60,
                'last_started_at' => '2026-07-14 08:18:35',
                'last_finished_at' => '2026-07-14 08:19:04',
                'last_failed_at' => '2026-07-13 20:02:07',
                'last_success_at' => '2026-07-14 08:19:04',
                'last_error' => NULL,
                'metadata' => '{"exception":"TypeError","last_stats":{"checked":6187,"fetched":5,"created":0,"updated":5,"failed":11,"skipped":6175,"metadata":{"platform":"atcoder","entity":"problem"}}}',
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-14 08:19:04',
            ),
            9 => 
            array (
                'id' => 10,
                'platform_id' => 2,
                'entity' => 'user',
                'enabled' => 1,
                'priority' => 80,
                'interval_minutes' => 60,
                'last_started_at' => '2026-07-14 08:19:08',
                'last_finished_at' => '2026-07-14 08:19:08',
                'last_failed_at' => '2026-07-13 20:02:13',
                'last_success_at' => '2026-07-14 08:19:08',
                'last_error' => NULL,
                'metadata' => '{"exception":"TypeError","last_stats":{"checked":2,"fetched":0,"created":0,"updated":0,"failed":0,"skipped":2,"metadata":{"platform":"atcoder","entity":"user"}}}',
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-14 08:19:08',
            ),
            10 => 
            array (
                'id' => 11,
                'platform_id' => 2,
                'entity' => 'submission',
                'enabled' => 0,
                'priority' => 95,
                'interval_minutes' => 30,
                'last_started_at' => NULL,
                'last_finished_at' => NULL,
                'last_failed_at' => NULL,
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-11 17:44:29',
            ),
            11 => 
            array (
                'id' => 12,
                'platform_id' => 2,
                'entity' => 'standing',
                'enabled' => 1,
                'priority' => 90,
                'interval_minutes' => 30,
                'last_started_at' => '2026-07-14 08:19:04',
                'last_finished_at' => '2026-07-14 08:19:04',
                'last_failed_at' => '2026-07-14 08:19:04',
                'last_success_at' => NULL,
            'last_error' => 'Call to undefined method App\\Platforms\\AtCoder\\AtCoderAdapter::standingImporter()',
                'metadata' => '{"exception":"Error"}',
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-14 08:19:04',
            ),
            12 => 
            array (
                'id' => 13,
                'platform_id' => 2,
                'entity' => 'rating_change',
                'enabled' => 0,
                'priority' => 90,
                'interval_minutes' => 30,
                'last_started_at' => NULL,
                'last_finished_at' => NULL,
                'last_failed_at' => NULL,
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-11 17:44:29',
            ),
            13 => 
            array (
                'id' => 14,
                'platform_id' => 2,
                'entity' => 'user_rating_history',
                'enabled' => 1,
                'priority' => 90,
                'interval_minutes' => 30,
                'last_started_at' => '2026-07-14 08:19:04',
                'last_finished_at' => '2026-07-14 08:19:08',
                'last_failed_at' => NULL,
                'last_success_at' => '2026-07-14 08:19:08',
                'last_error' => NULL,
                'metadata' => '{"last_stats":{"checked":2,"fetched":157,"created":157,"updated":0,"failed":0,"skipped":0,"metadata":{"platform":"atcoder","entity":"user_rating_history"}}}',
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-14 08:19:08',
            ),
        ));
        
        
    }
}