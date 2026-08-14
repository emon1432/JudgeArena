<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SubmissionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('submissions')->delete();
        
        
        
    }
}