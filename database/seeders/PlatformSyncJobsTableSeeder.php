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
                'last_started_at' => '2026-09-03 16:41:10',
                'last_finished_at' => '2026-09-03 16:42:32',
                'last_failed_at' => NULL,
                'last_success_at' => '2026-09-03 16:42:32',
                'last_error' => NULL,
                'metadata' => '{"last_stats":{"checked":2143,"fetched":2143,"created":2143,"updated":0,"failed":0,"skipped":0,"metadata":{"platform":"codeforces","entity":"contest"}}}',
                'created_at' => '2026-08-14 17:02:44',
                'updated_at' => '2026-09-03 16:42:32',
            ),
            1 => 
            array (
                'id' => 2,
                'platform_id' => 1,
                'entity' => 'problem',
                'enabled' => 0,
                'priority' => 90,
                'interval_minutes' => 15,
                'last_started_at' => NULL,
                'last_finished_at' => NULL,
                'last_failed_at' => NULL,
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
                'created_at' => '2026-08-14 17:02:44',
                'updated_at' => '2026-08-27 10:21:14',
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
                'updated_at' => '2026-08-24 15:25:02',
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
                'updated_at' => '2026-08-24 14:40:01',
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
                'updated_at' => '2026-08-24 15:21:10',
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
                'updated_at' => '2026-08-24 14:50:03',
            ),
            6 => 
            array (
                'id' => 7,
                'platform_id' => 2,
                'entity' => 'contest',
                'enabled' => 1,
                'priority' => 100,
                'interval_minutes' => 15,
                'last_started_at' => '2026-09-03 16:42:32',
                'last_finished_at' => '2026-09-03 16:46:43',
                'last_failed_at' => NULL,
                'last_success_at' => '2026-09-03 16:46:43',
                'last_error' => NULL,
                'metadata' => '{"last_stats":{"checked":6397,"fetched":6397,"created":6397,"updated":0,"failed":0,"skipped":0,"metadata":[]}}',
                'created_at' => '2026-08-18 05:56:17',
                'updated_at' => '2026-09-03 16:46:43',
            ),
            7 => 
            array (
                'id' => 8,
                'platform_id' => 2,
                'entity' => 'problem',
                'enabled' => 0,
                'priority' => 90,
                'interval_minutes' => 15,
                'last_started_at' => NULL,
                'last_finished_at' => NULL,
                'last_failed_at' => NULL,
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
                'created_at' => '2026-08-18 05:56:17',
                'updated_at' => '2026-08-27 10:21:14',
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
                'updated_at' => '2026-08-20 10:16:12',
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
                'created_at' => '2026-08-18 05:56:17',
                'updated_at' => '2026-08-20 10:16:13',
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
                'created_at' => '2026-08-18 05:56:17',
                'updated_at' => '2026-08-20 10:16:13',
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
                'created_at' => '2026-08-18 05:56:17',
                'updated_at' => '2026-08-20 10:16:13',
            ),
        ));
        
        
    }
}