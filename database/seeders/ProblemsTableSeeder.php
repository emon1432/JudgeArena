<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProblemsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('problems')->delete();
        
        
        
    }
}