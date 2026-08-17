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

        \DB::table('platform_sync_jobs')->insert(array(
            0 =>
            array(
                'id' => 1,
                'platform_id' => 1,
                'entity' => 'contest',
                'enabled' => 1,
                'priority' => 100,
                'interval_minutes' => 15,
                'last_started_at' => '2026-08-15 04:02:01',
                'last_finished_at' => '2026-08-15 04:02:04',
                'last_failed_at' => NULL,
                'last_success_at' => '2026-08-15 04:02:04',
                'last_error' => NULL,
                'metadata' => '{"last_stats":{"checked":2139,"fetched":2139,"created":0,"updated":2,"failed":0,"skipped":2137,"metadata":{"platform":"codeforces","entity":"contest"}}}',
                'created_at' => '2026-08-14 17:02:44',
                'updated_at' => '2026-08-15 04:02:04',
            ),
            1 =>
            array(
                'id' => 2,
                'platform_id' => 1,
                'entity' => 'problem',
                'enabled' => 1,
                'priority' => 90,
                'interval_minutes' => 15,
                'last_started_at' => '2026-08-15 03:52:02',
                'last_finished_at' => '2026-08-15 03:52:32',
                'last_failed_at' => '2026-08-14 19:38:07',
                'last_success_at' => '2026-08-15 03:52:32',
                'last_error' => NULL,
                'metadata' => '{"exception":"Illuminate\\\\Database\\\\UniqueConstraintViolationException","last_stats":{"checked":2139,"fetched":1,"created":0,"updated":1,"failed":23,"skipped":2115,"metadata":{"platform":"codeforces","entity":"problem"}}}',
                'created_at' => '2026-08-14 17:02:44',
                'updated_at' => '2026-08-15 03:52:32',
            ),
            2 =>
            array(
                'id' => 3,
                'platform_id' => 1,
                'entity' => 'user',
                'enabled' => 1,
                'priority' => 80,
                'interval_minutes' => 30,
                'last_started_at' => '2026-08-15 03:59:01',
                'last_finished_at' => '2026-08-15 03:59:01',
                'last_failed_at' => NULL,
                'last_success_at' => '2026-08-15 03:59:01',
                'last_error' => NULL,
                'metadata' => '{"last_stats":{"checked":2,"fetched":0,"created":0,"updated":0,"failed":0,"skipped":2,"metadata":{"platform":"codeforces","entity":"user"}}}',
                'created_at' => '2026-08-14 17:02:44',
                'updated_at' => '2026-08-15 03:59:01',
            ),
            3 =>
            array(
                'id' => 4,
                'platform_id' => 1,
                'entity' => 'user_rating_history',
                'enabled' => 1,
                'priority' => 70,
                'interval_minutes' => 120,
                'last_started_at' => '2026-08-15 03:27:01',
                'last_finished_at' => '2026-08-15 03:27:01',
                'last_failed_at' => NULL,
                'last_success_at' => '2026-08-15 03:27:01',
                'last_error' => NULL,
                'metadata' => '{"last_stats":{"checked":2,"fetched":0,"created":0,"updated":0,"failed":0,"skipped":2,"metadata":{"platform":"codeforces","entity":"user_rating_history"}}}',
                'created_at' => '2026-08-14 17:02:44',
                'updated_at' => '2026-08-15 03:27:01',
            ),
            4 =>
            array(
                'id' => 5,
                'platform_id' => 1,
                'entity' => 'user_submissions',
                'enabled' => 1,
                'priority' => 60,
                'interval_minutes' => 30,
                'last_started_at' => '2026-08-15 03:37:01',
                'last_finished_at' => '2026-08-15 03:37:02',
                'last_failed_at' => NULL,
                'last_success_at' => '2026-08-15 03:37:02',
                'last_error' => NULL,
                'metadata' => '{"last_stats":{"checked":2,"fetched":0,"created":0,"updated":0,"failed":0,"skipped":2,"metadata":{"platform":"codeforces","entity":"user_submission"}}}',
                'created_at' => '2026-08-14 17:02:44',
                'updated_at' => '2026-08-15 03:37:02',
            ),
            5 =>
            array(
                'id' => 6,
                'platform_id' => 1,
                'entity' => 'user_standings',
                'enabled' => 1,
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
            array(
                'id' => 7,
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
                'created_at' => now(),
                'updated_at' => now(),
            ),
            7 =>
            array(
                'id' => 8,
                'platform_id' => 2,
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
                'created_at' => now(),
                'updated_at' => now(),
            ),
        ));
    }
}
