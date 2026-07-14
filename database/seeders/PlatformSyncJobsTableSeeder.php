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
                'last_started_at' => '2026-07-14 10:53:21',
                'last_finished_at' => '2026-07-14 10:53:24',
                'last_failed_at' => '2026-07-13 13:50:41',
                'last_success_at' => '2026-07-14 10:53:24',
                'last_error' => NULL,
                'metadata' => '{"exception":"TypeError","last_stats":{"checked":2130,"fetched":2130,"created":1,"updated":0,"failed":0,"skipped":2129,"metadata":{"platform":"codeforces","entity":"contest"}}}',
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-14 10:53:24',
            ),
            1 => 
            array (
                'id' => 2,
                'platform_id' => 1,
                'entity' => 'problem',
                'enabled' => 1,
                'priority' => 90,
                'interval_minutes' => 30,
                'last_started_at' => '2026-07-14 10:55:56',
                'last_finished_at' => '2026-07-14 10:57:29',
                'last_failed_at' => '2026-07-13 16:05:44',
                'last_success_at' => '2026-07-14 10:57:29',
                'last_error' => NULL,
                'metadata' => '{"exception":"TypeError","last_stats":{"checked":2130,"fetched":0,"created":0,"updated":0,"failed":26,"skipped":2104,"metadata":{"platform":"codeforces","entity":"problem"}}}',
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-14 10:57:29',
            ),
            2 => 
            array (
                'id' => 3,
                'platform_id' => 1,
                'entity' => 'user',
                'enabled' => 1,
                'priority' => 80,
                'interval_minutes' => 60,
                'last_started_at' => '2026-07-14 10:57:56',
                'last_finished_at' => '2026-07-14 10:57:56',
                'last_failed_at' => '2026-07-13 20:02:11',
                'last_success_at' => '2026-07-14 10:57:56',
                'last_error' => NULL,
                'metadata' => '{"exception":"TypeError","last_stats":{"checked":2,"fetched":0,"created":0,"updated":0,"failed":0,"skipped":2,"metadata":{"platform":"codeforces","entity":"user"}}}',
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-14 10:57:56',
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
                'enabled' => 0,
                'priority' => 90,
                'interval_minutes' => 30,
                'last_started_at' => '2026-07-14 08:18:29',
                'last_finished_at' => '2026-07-14 08:18:29',
                'last_failed_at' => '2026-07-14 08:18:29',
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
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
                'last_started_at' => '2026-07-14 10:57:29',
                'last_finished_at' => '2026-07-14 10:57:29',
                'last_failed_at' => NULL,
                'last_success_at' => '2026-07-14 10:57:29',
                'last_error' => NULL,
                'metadata' => '{"last_stats":{"checked":2,"fetched":0,"created":0,"updated":0,"failed":0,"skipped":2,"metadata":{"platform":"codeforces","entity":"user_rating_history"}}}',
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-14 10:57:29',
            ),
            7 => 
            array (
                'id' => 8,
                'platform_id' => 2,
                'entity' => 'contest',
                'enabled' => 1,
                'priority' => 100,
                'interval_minutes' => 15,
                'last_started_at' => '2026-07-14 10:53:24',
                'last_finished_at' => '2026-07-14 10:55:56',
                'last_failed_at' => '2026-07-14 08:17:08',
                'last_success_at' => '2026-07-14 10:55:56',
                'last_error' => NULL,
                'metadata' => '{"exception":"Illuminate\\\\Http\\\\Client\\\\ConnectionException","last_stats":{"checked":6192,"fetched":6192,"created":4,"updated":0,"failed":0,"skipped":6188,"metadata":{"platform":"atcoder","entity":"contest"}}}',
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-14 10:55:56',
            ),
            8 => 
            array (
                'id' => 9,
                'platform_id' => 2,
                'entity' => 'problem',
                'enabled' => 1,
                'priority' => 90,
                'interval_minutes' => 60,
                'last_started_at' => '2026-07-14 10:57:29',
                'last_finished_at' => '2026-07-14 10:57:56',
                'last_failed_at' => '2026-07-13 20:02:07',
                'last_success_at' => '2026-07-14 10:57:56',
                'last_error' => NULL,
                'metadata' => '{"exception":"TypeError","last_stats":{"checked":6191,"fetched":28,"created":0,"updated":28,"failed":11,"skipped":6176,"metadata":{"platform":"atcoder","entity":"problem"}}}',
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-14 10:57:56',
            ),
            9 => 
            array (
                'id' => 10,
                'platform_id' => 2,
                'entity' => 'user',
                'enabled' => 1,
                'priority' => 80,
                'interval_minutes' => 60,
                'last_started_at' => '2026-07-14 10:57:56',
                'last_finished_at' => '2026-07-14 10:57:56',
                'last_failed_at' => '2026-07-13 20:02:13',
                'last_success_at' => '2026-07-14 10:57:56',
                'last_error' => NULL,
                'metadata' => '{"exception":"TypeError","last_stats":{"checked":2,"fetched":0,"created":0,"updated":0,"failed":0,"skipped":2,"metadata":{"platform":"atcoder","entity":"user"}}}',
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-14 10:57:56',
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
                'enabled' => 0,
                'priority' => 90,
                'interval_minutes' => 30,
                'last_started_at' => '2026-07-14 08:19:04',
                'last_finished_at' => '2026-07-14 08:19:04',
                'last_failed_at' => '2026-07-14 08:19:04',
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
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
                'last_started_at' => '2026-07-14 10:57:56',
                'last_finished_at' => '2026-07-14 10:57:56',
                'last_failed_at' => NULL,
                'last_success_at' => '2026-07-14 10:57:56',
                'last_error' => NULL,
                'metadata' => '{"last_stats":{"checked":2,"fetched":0,"created":0,"updated":0,"failed":0,"skipped":2,"metadata":{"platform":"atcoder","entity":"user_rating_history"}}}',
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-14 10:57:56',
            ),
        ));
        
        
    }
}