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
                'last_synced_at' => NULL,
                'created_at' => '2026-05-31 14:49:48',
                'updated_at' => '2026-05-31 14:49:48',
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
                'last_synced_at' => NULL,
                'created_at' => '2026-05-31 14:49:48',
                'updated_at' => '2026-05-31 14:49:48',
            ),
        ));
        
        
    }
}