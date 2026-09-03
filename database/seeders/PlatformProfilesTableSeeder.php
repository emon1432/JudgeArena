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
                'platform_id' => 2,
                'handle' => 'tourist',
                'raw' => NULL,
                'metadata' => NULL,
                'status' => 'Active',
                'last_synced_at' => '2026-09-02 15:09:50',
                'created_at' => '2026-05-31 14:49:48',
                'updated_at' => '2026-09-02 15:09:50',
            ),
            1 => 
            array (
                'id' => 2,
                'user_id' => 2,
                'platform_id' => 1,
                'handle' => 'tourist',
                'raw' => NULL,
                'metadata' => NULL,
                'status' => 'Active',
                'last_synced_at' => '2026-08-23 10:03:33',
                'created_at' => '2026-05-31 14:49:48',
                'updated_at' => '2026-08-23 10:03:33',
            ),
            2 => 
            array (
                'id' => 3,
                'user_id' => 3,
                'platform_id' => 2,
                'handle' => 'e_mon',
                'raw' => NULL,
                'metadata' => NULL,
                'status' => 'Active',
                'last_synced_at' => '2026-07-13 20:02:12',
                'created_at' => '2026-06-01 12:40:13',
                'updated_at' => '2026-07-13 20:02:12',
            ),
            3 => 
            array (
                'id' => 4,
                'user_id' => 3,
                'platform_id' => 1,
                'handle' => 'emon_mon',
                'raw' => NULL,
                'metadata' => NULL,
                'status' => 'Active',
                'last_synced_at' => '2026-08-23 10:03:33',
                'created_at' => '2026-06-01 12:40:13',
                'updated_at' => '2026-08-23 10:03:33',
            ),
        ));
        
        
    }
}