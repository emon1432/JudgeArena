<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ApplicationLogsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('application_logs')->delete();
        
        \DB::table('application_logs')->insert(array (
            0 => 
            array (
                'id' => 1,
                'level' => 'info',
                'category' => 'sync',
                'platform' => NULL,
                'entity_type' => NULL,
                'entity_id' => NULL,
                'message' => 'Platform synchronization command started',
                'context' => '{"category":"sync","source":"App\\\\Console\\\\Commands\\\\SyncCommand"}',
                'source' => 'App\\Console\\Commands\\SyncCommand',
                'user_id' => NULL,
                'ip_address' => '127.0.0.1',
                'created_at' => '2026-09-03 16:41:10',
            ),
            1 => 
            array (
                'id' => 2,
                'level' => 'info',
                'category' => 'sync',
                'platform' => NULL,
                'entity_type' => NULL,
                'entity_id' => NULL,
                'message' => 'Platform synchronization command completed',
                'context' => '{"category":"sync","source":"App\\\\Console\\\\Commands\\\\SyncCommand","due_jobs":2,"successful":2,"failed":0,"skipped":0}',
                'source' => 'App\\Console\\Commands\\SyncCommand',
                'user_id' => NULL,
                'ip_address' => '127.0.0.1',
                'created_at' => '2026-09-03 16:46:43',
            ),
        ));
        
        
    }
}