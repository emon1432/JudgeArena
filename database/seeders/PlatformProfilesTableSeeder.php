<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PlatformProfilesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('platform_profiles')->delete();

        \DB::table('platform_profiles')->insert(array (
            0 =>
            array (
                'id' => 1,
                'user_id' => 2,
                'platform_id' => 3,
                'handle' => 'tourist',
                'rating' => NULL,
                'total_solved' => 0,
                'profile_data' => NULL,
                'profile_url' => 'https://atcoder.jp/users/tourist',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-06 11:27:33',
                'updated_at' => '2026-02-08 16:06:46',
            ),
            1 =>
            array (
                'id' => 2,
                'user_id' => 2,
                'platform_id' => 4,
                'handle' => 'potato167',
                'rating' => NULL,
                'total_solved' => 0,
                'profile_data' => NULL,
                'profile_url' => 'https://www.codechef.com/users/potato167',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-06 11:27:33',
                'updated_at' => '2026-02-08 16:06:46',
            ),
            2 =>
            array (
                'id' => 3,
                'user_id' => 2,
                'platform_id' => 1,
                'handle' => 'tourist',
                'rating' => NULL,
                'total_solved' => 0,
                'profile_data' => NULL,
                'profile_url' => 'https://codeforces.com/profile/tourist',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-06 11:27:33',
                'updated_at' => '2026-02-08 16:06:46',
            ),
            3 =>
            array (
                'id' => 4,
                'user_id' => 2,
                'platform_id' => 7,
                'handle' => 'lboris',
                'rating' => NULL,
                'total_solved' => 0,
                'profile_data' => NULL,
                'profile_url' => 'https://www.hackerearth.com/@lboris',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-06 11:27:33',
                'updated_at' => '2026-02-08 16:06:47',
            ),
            4 =>
            array (
                'id' => 5,
                'user_id' => 2,
                'platform_id' => 6,
                'handle' => 'Gennady',
                'rating' => NULL,
                'total_solved' => 0,
                'profile_data' => NULL,
                'profile_url' => 'https://www.hackerrank.com/profile/Gennady',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-06 11:27:33',
                'updated_at' => '2026-02-08 16:06:47',
            ),
            5 =>
            array (
                'id' => 6,
                'user_id' => 2,
                'platform_id' => 2,
                'handle' => 'cpcs',
                'rating' => NULL,
                'total_solved' => 0,
                'profile_data' => NULL,
                'profile_url' => 'https://leetcode.com/u/cpcs',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-06 11:27:33',
                'updated_at' => '2026-02-08 16:06:47',
            ),
            6 =>
            array (
                'id' => 7,
                'user_id' => 2,
                'platform_id' => 5,
                'handle' => 'defrager',
                'rating' => NULL,
                'total_solved' => 0,
                'profile_data' => NULL,
                'profile_url' => 'https://www.spoj.com/users/defrager',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-06 11:27:33',
                'updated_at' => '2026-02-08 16:06:47',
            ),
            7 =>
            array (
                'id' => 8,
                'user_id' => 2,
                'platform_id' => 9,
                'handle' => '19306',
                'rating' => NULL,
                'total_solved' => 0,
                'profile_data' => NULL,
                'profile_url' => 'https://acm.timus.ru/author.aspx?id=19306',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-06 11:27:33',
                'updated_at' => '2026-02-08 16:06:47',
            ),
            8 =>
            array (
                'id' => 9,
                'user_id' => 2,
                'platform_id' => 8,
                'handle' => '1133',
                'rating' => NULL,
                'total_solved' => 0,
                'profile_data' => NULL,
                'profile_url' => 'https://uhunt.onlinejudge.org/id/1133',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-06 11:27:33',
                'updated_at' => '2026-02-08 16:06:47',
            ),
            9 =>
            array (
                'id' => 10,
                'user_id' => 3,
                'platform_id' => 3,
                'handle' => 'e_mon',
                'rating' => NULL,
                'total_solved' => 0,
                'profile_data' => NULL,
                'profile_url' => 'https://atcoder.jp/users/e_mon',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-09 08:28:10',
                'updated_at' => '2026-02-09 08:29:02',
            ),
            10 =>
            array (
                'id' => 11,
                'user_id' => 3,
                'platform_id' => 4,
                'handle' => 'e_mon',
                'rating' => NULL,
                'total_solved' => 0,
                'profile_data' => NULL,
                'profile_url' => 'https://www.codechef.com/users/e_mon',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-09 08:28:10',
                'updated_at' => '2026-02-09 08:29:05',
            ),
            11 =>
            array (
                'id' => 12,
                'user_id' => 3,
                'platform_id' => 1,
                'handle' => 'emon_mon',
                'rating' => NULL,
                'total_solved' => 0,
                'profile_data' => NULL,
                'profile_url' => 'https://codeforces.com/profile/emon_mon',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-09 08:28:10',
                'updated_at' => '2026-02-09 08:29:12',
            ),
            12 =>
            array (
                'id' => 13,
                'user_id' => 3,
                'platform_id' => 7,
                'handle' => 'e_mon',
                'rating' => NULL,
                'total_solved' => 0,
                'profile_data' => NULL,
                'profile_url' => 'https://www.hackerearth.com/@e_mon',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-09 08:28:10',
                'updated_at' => '2026-02-09 08:29:28',
            ),
            13 =>
            array (
                'id' => 14,
                'user_id' => 3,
                'platform_id' => 6,
                'handle' => 'e_mon',
                'rating' => NULL,
                'total_solved' => 0,
                'profile_data' => NULL,
                'profile_url' => 'https://www.hackerrank.com/profile/e_mon',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-09 08:28:10',
                'updated_at' => '2026-02-09 08:29:50',
            ),
            14 =>
            array (
                'id' => 15,
                'user_id' => 3,
                'platform_id' => 2,
                'handle' => 'emon1432',
                'rating' => NULL,
                'total_solved' => 0,
                'profile_data' => NULL,
                'profile_url' => 'https://leetcode.com/u/emon1432',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-09 08:28:10',
                'updated_at' => '2026-02-09 08:29:57',
            ),
            15 =>
            array (
                'id' => 16,
                'user_id' => 3,
                'platform_id' => 5,
                'handle' => 'e_mon',
                'rating' => NULL,
                'total_solved' => 0,
                'profile_data' => NULL,
                'profile_url' => 'https://www.spoj.com/users/e_mon',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-09 08:28:10',
                'updated_at' => '2026-02-09 08:30:28',
            ),
            16 =>
            array (
                'id' => 17,
                'user_id' => 3,
                'platform_id' => 9,
                'handle' => '405359',
                'rating' => NULL,
                'total_solved' => 0,
                'profile_data' => NULL,
                'profile_url' => 'https://acm.timus.ru/author.aspx?id=405359',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-09 08:28:10',
                'updated_at' => '2026-02-09 08:30:37',
            ),
            17 =>
            array (
                'id' => 18,
                'user_id' => 3,
                'platform_id' => 8,
                'handle' => '1135924',
                'rating' => NULL,
                'total_solved' => 0,
                'profile_data' => NULL,
                'profile_url' => 'https://uhunt.onlinejudge.org/id/1135924',
                'status' => 'Active',
                'last_synced_at' => NULL,
                'created_at' => '2026-02-09 08:28:10',
                'updated_at' => '2026-02-09 08:31:11',
            ),
        ));


    }
}
