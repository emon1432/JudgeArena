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
                'last_started_at' => NULL,
                'last_finished_at' => NULL,
                'last_failed_at' => NULL,
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-11 17:44:29',
            ),
            1 =>
            array (
                'id' => 2,
                'platform_id' => 1,
                'entity' => 'problem',
                'enabled' => 1,
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
            2 =>
            array (
                'id' => 3,
                'platform_id' => 1,
                'entity' => 'user',
                'enabled' => 1,
                'priority' => 80,
                'interval_minutes' => 60,
                'last_started_at' => NULL,
                'last_finished_at' => NULL,
                'last_failed_at' => NULL,
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-11 17:44:29',
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
                'last_started_at' => NULL,
                'last_finished_at' => NULL,
                'last_failed_at' => NULL,
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-11 17:44:29',
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
                'last_started_at' => NULL,
                'last_finished_at' => NULL,
                'last_failed_at' => NULL,
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-11 17:44:29',
            ),
            7 =>
            array (
                'id' => 8,
                'platform_id' => 2,
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
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-11 17:44:29',
            ),
            8 =>
            array (
                'id' => 9,
                'platform_id' => 2,
                'entity' => 'problem',
                'enabled' => 1,
                'priority' => 90,
                'interval_minutes' => 60,
                'last_started_at' => NULL,
                'last_finished_at' => NULL,
                'last_failed_at' => NULL,
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-11 17:44:29',
            ),
            9 =>
            array (
                'id' => 10,
                'platform_id' => 2,
                'entity' => 'user',
                'enabled' => 1,
                'priority' => 80,
                'interval_minutes' => 60,
                'last_started_at' => NULL,
                'last_finished_at' => NULL,
                'last_failed_at' => NULL,
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-11 17:44:29',
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
                'last_started_at' => NULL,
                'last_finished_at' => NULL,
                'last_failed_at' => NULL,
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-11 17:44:29',
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
                'last_started_at' => NULL,
                'last_finished_at' => NULL,
                'last_failed_at' => NULL,
                'last_success_at' => NULL,
                'last_error' => NULL,
                'metadata' => NULL,
                'created_at' => '2026-07-11 17:44:29',
                'updated_at' => '2026-07-11 17:44:29',
            ),
        ));


    }
}
