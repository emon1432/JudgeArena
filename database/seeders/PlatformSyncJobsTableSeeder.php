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
                'last_started_at' => '2026-09-06 19:16:07',
                'last_finished_at' => '2026-09-06 19:16:13',
                'last_failed_at' => NULL,
                'last_success_at' => '2026-09-06 19:16:13',
                'last_error' => NULL,
                'metadata' => '{"last_stats":{"checked":2146,"fetched":2146,"created":0,"updated":5,"failed":0,"skipped":2141,"metadata":{"platform":"codeforces","entity":"contest"}}}',
                'created_at' => '2026-08-14 17:02:44',
                'updated_at' => '2026-09-06 19:16:13',
            ),
            1 => 
            array (
                'id' => 2,
                'platform_id' => 1,
                'entity' => 'problem',
                'enabled' => 1,
                'priority' => 90,
                'interval_minutes' => 15,
                'last_started_at' => '2026-09-06 19:16:24',
                'last_finished_at' => '2026-09-06 19:17:20',
                'last_failed_at' => NULL,
                'last_success_at' => '2026-09-06 19:17:20',
                'last_error' => NULL,
                'metadata' => '{"last_stats":{"checked":2146,"fetched":8,"created":8,"updated":0,"failed":27,"skipped":2118,"metadata":{"platform":"codeforces","entity":"problem"}}}',
                'created_at' => '2026-08-14 17:02:44',
                'updated_at' => '2026-09-06 19:17:20',
            ),
            2 => 
            array (
                'id' => 3,
                'platform_id' => 1,
                'entity' => 'user',
                'enabled' => 0,
                'priority' => 80,
                'interval_minutes' => 30,
                'last_started_at' => NULL,
                'last_finished_at' => NULL,
                'last_failed_at' => NULL,
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
                'created_at' => '2026-08-14 17:02:44',
                'updated_at' => '2026-08-14 17:02:44',
            ),
            3 => 
            array (
                'id' => 4,
                'platform_id' => 1,
                'entity' => 'user_rating_history',
                'enabled' => 0,
                'priority' => 70,
                'interval_minutes' => 120,
                'last_started_at' => NULL,
                'last_finished_at' => NULL,
                'last_failed_at' => NULL,
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
                'created_at' => '2026-08-14 17:02:44',
                'updated_at' => '2026-08-14 17:02:44',
            ),
            4 => 
            array (
                'id' => 5,
                'platform_id' => 1,
                'entity' => 'user_submissions',
                'enabled' => 0,
                'priority' => 60,
                'interval_minutes' => 30,
                'last_started_at' => NULL,
                'last_finished_at' => NULL,
                'last_failed_at' => NULL,
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
                'created_at' => '2026-08-14 17:02:44',
                'updated_at' => '2026-08-14 17:02:44',
            ),
            5 => 
            array (
                'id' => 6,
                'platform_id' => 1,
                'entity' => 'user_standings',
                'enabled' => 0,
                'priority' => 50,
                'interval_minutes' => 60,
                'last_started_at' => NULL,
                'last_finished_at' => NULL,
                'last_failed_at' => NULL,
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
                'created_at' => '2026-08-14 17:02:44',
                'updated_at' => '2026-08-14 17:02:44',
            ),
            6 => 
            array (
                'id' => 7,
                'platform_id' => 2,
                'entity' => 'contest',
                'enabled' => 1,
                'priority' => 100,
                'interval_minutes' => 15,
                'last_started_at' => '2026-09-06 19:16:13',
                'last_finished_at' => '2026-09-06 19:16:24',
                'last_failed_at' => NULL,
                'last_success_at' => '2026-09-06 19:16:24',
                'last_error' => NULL,
                'metadata' => '{"last_stats":{"checked":6405,"fetched":6405,"created":0,"updated":17,"failed":0,"skipped":6388,"metadata":[]}}',
                'created_at' => '2026-08-14 17:02:44',
                'updated_at' => '2026-09-06 19:16:24',
            ),
            7 => 
            array (
                'id' => 8,
                'platform_id' => 2,
                'entity' => 'problem',
                'enabled' => 1,
                'priority' => 90,
                'interval_minutes' => 15,
                'last_started_at' => '2026-09-06 18:43:28',
                'last_finished_at' => '2026-09-06 19:14:43',
                'last_failed_at' => NULL,
                'last_success_at' => '2026-09-06 19:14:43',
                'last_error' => NULL,
                'metadata' => '{"last_stats":{"checked":6405,"fetched":39370,"created":9488,"updated":29882,"failed":0,"skipped":0,"metadata":{"platform":"atcoder","entity":"problem"}}}',
                'created_at' => '2026-08-14 17:02:44',
                'updated_at' => '2026-09-06 19:14:43',
            ),
            8 => 
            array (
                'id' => 9,
                'platform_id' => 2,
                'entity' => 'user',
                'enabled' => 0,
                'priority' => 80,
                'interval_minutes' => 30,
                'last_started_at' => NULL,
                'last_finished_at' => NULL,
                'last_failed_at' => NULL,
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
                'created_at' => '2026-08-14 17:02:44',
                'updated_at' => '2026-08-14 17:02:44',
            ),
            9 => 
            array (
                'id' => 10,
                'platform_id' => 2,
                'entity' => 'user_rating_history',
                'enabled' => 0,
                'priority' => 70,
                'interval_minutes' => 120,
                'last_started_at' => NULL,
                'last_finished_at' => NULL,
                'last_failed_at' => NULL,
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
                'created_at' => '2026-08-14 17:02:44',
                'updated_at' => '2026-08-14 17:02:44',
            ),
            10 => 
            array (
                'id' => 11,
                'platform_id' => 2,
                'entity' => 'user_submissions',
                'enabled' => 0,
                'priority' => 60,
                'interval_minutes' => 30,
                'last_started_at' => NULL,
                'last_finished_at' => NULL,
                'last_failed_at' => NULL,
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
                'created_at' => '2026-08-14 17:02:44',
                'updated_at' => '2026-08-14 17:02:44',
            ),
            11 => 
            array (
                'id' => 12,
                'platform_id' => 2,
                'entity' => 'user_standings',
                'enabled' => 0,
                'priority' => 50,
                'interval_minutes' => 60,
                'last_started_at' => NULL,
                'last_finished_at' => NULL,
                'last_failed_at' => NULL,
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
                'created_at' => '2026-08-14 17:02:44',
                'updated_at' => '2026-08-14 17:02:44',
            ),
        ));
        
        
    }
}