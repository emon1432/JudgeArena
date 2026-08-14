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
                'id' => 26,
                'platform_id' => 1,
                'entity' => 'contest',
                'enabled' => 1,
                'priority' => 100,
                'interval_minutes' => 15,
                'last_started_at' => NULL,
                'last_finished_at' => NULL,
                'last_failed_at' => NULL,
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
                'created_at' => '2026-08-14 17:02:44',
                'updated_at' => '2026-08-14 17:02:44',
            ),
            1 => 
            array (
                'id' => 27,
                'platform_id' => 1,
                'entity' => 'problem',
                'enabled' => 1,
                'priority' => 90,
                'interval_minutes' => 15,
                'last_started_at' => NULL,
                'last_finished_at' => NULL,
                'last_failed_at' => NULL,
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
                'created_at' => '2026-08-14 17:02:44',
                'updated_at' => '2026-08-14 17:02:44',
            ),
            2 => 
            array (
                'id' => 28,
                'platform_id' => 1,
                'entity' => 'user',
                'enabled' => 1,
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
                'id' => 29,
                'platform_id' => 1,
                'entity' => 'submission',
                'enabled' => 0,
                'priority' => 70,
                'interval_minutes' => 5,
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
                'id' => 30,
                'platform_id' => 1,
                'entity' => 'standing',
                'enabled' => 0,
                'priority' => 60,
                'interval_minutes' => 15,
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
                'id' => 31,
                'platform_id' => 1,
                'entity' => 'rating_change',
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
                'id' => 32,
                'platform_id' => 1,
                'entity' => 'user_rating_history',
                'enabled' => 1,
                'priority' => 40,
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
            7 => 
            array (
                'id' => 33,
                'platform_id' => 1,
                'entity' => 'user_submissions',
                'enabled' => 1,
                'priority' => 30,
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
            8 => 
            array (
                'id' => 34,
                'platform_id' => 1,
                'entity' => 'user_standings',
                'enabled' => 1,
                'priority' => 20,
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