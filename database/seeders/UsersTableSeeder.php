<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('users')->delete();
        
        \DB::table('users')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Emon Admin',
                'username' => 'emonadmin',
                'role' => 'admin',
                'email' => 'admin@judgearena.com',
                'phone' => NULL,
                'date_of_birth' => NULL,
                'gender' => NULL,
                'country_id' => NULL,
                'institute_id' => NULL,
                'fav_quote' => NULL,
                'website' => NULL,
                'facebook' => NULL,
                'instagram' => NULL,
                'twitter' => NULL,
                'github' => NULL,
                'linkedin' => NULL,
                'email_verified_at' => NULL,
                'password' => '$2y$12$atEjRCnSoCeKnbOCT6a.p.EWTQ7GzU97eInXEyEp0OHnVd6vH4Dnm',
                'two_factor_secret' => NULL,
                'two_factor_recovery_codes' => NULL,
                'two_factor_confirmed_at' => NULL,
                'remember_token' => NULL,
                'current_team_id' => NULL,
                'image' => NULL,
                'last_synced_at' => NULL,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Top Users',
                'username' => 'topusers',
                'role' => 'user',
                'email' => 'topusers@judgearena.com',
                'phone' => '01700000000',
                'date_of_birth' => '1998-03-14',
                'gender' => 'Male',
                'country_id' => 19,
                'institute_id' => 10200,
                'fav_quote' => 'Assalamu Alaikum',
                'website' => 'emonideas.com',
                'facebook' => 'emon143298',
                'instagram' => 'emon143298',
                'twitter' => 'emon14321',
                'github' => 'emon1432',
                'linkedin' => 'khairul-islam-emon',
                'email_verified_at' => NULL,
                'password' => '$2y$12$atEjRCnSoCeKnbOCT6a.p.EWTQ7GzU97eInXEyEp0OHnVd6vH4Dnm',
                'two_factor_secret' => NULL,
                'two_factor_recovery_codes' => NULL,
                'two_factor_confirmed_at' => NULL,
                'remember_token' => NULL,
                'current_team_id' => NULL,
                'image' => 'uploads/users/khairul-islam-emon1748759077683bf225059ea.jpg',
                'last_synced_at' => NULL,
                'created_at' => NULL,
                'updated_at' => '2026-02-06 11:55:00',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Khairul Islam Emon',
                'username' => 'e_mon',
                'role' => 'user',
                'email' => 'e.mon143298@gmail.com',
                'phone' => '01638849305',
                'date_of_birth' => '1998-03-14',
                'gender' => 'Male',
                'country_id' => 19,
                'institute_id' => 10200,
                'fav_quote' => 'Assalamu Alaikum',
                'website' => 'emonideas.com',
                'facebook' => 'emon143298',
                'instagram' => 'emon143298',
                'twitter' => 'emon14321',
                'github' => 'emon1432',
                'linkedin' => 'khairul-islam-emon',
                'email_verified_at' => NULL,
                'password' => '$2y$12$atEjRCnSoCeKnbOCT6a.p.EWTQ7GzU97eInXEyEp0OHnVd6vH4Dnm',
                'two_factor_secret' => NULL,
                'two_factor_recovery_codes' => NULL,
                'two_factor_confirmed_at' => NULL,
                'remember_token' => NULL,
                'current_team_id' => NULL,
                'image' => 'uploads/users/khairul-islam-emon1748759077683bf225059ea.jpg',
                'last_synced_at' => NULL,
                'created_at' => NULL,
                'updated_at' => '2026-02-09 08:28:34',
            ),
        ));
        
        
    }
}