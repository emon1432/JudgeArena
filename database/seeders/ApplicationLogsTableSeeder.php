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
        
        
        
    }
}