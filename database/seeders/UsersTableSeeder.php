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

        \DB::table('users')->insert([
            0 => [
                'id' => 1,
                'name' => 'Emon Admin',
                'username' => 'emonadmin',
                'role' => 'admin',
                'email' => 'admin@judgearena.com',
                'phone' => null,
                'date_of_birth' => null,
                'gender' => null,
                'country_id' => null,
                'institute_id' => null,
                'fav_quote' => null,
                'website' => null,
                'facebook' => null,
                'instagram' => null,
                'twitter' => null,
                'github' => null,
                'linkedin' => null,
                'email_verified_at' => null,
                'password' => '$2y$12$atEjRCnSoCeKnbOCT6a.p.EWTQ7GzU97eInXEyEp0OHnVd6vH4Dnm',
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
                'remember_token' => 'uI0JHU4YdzSOExBsEIvnBKkI6zkkiFlzdzlm2YLLfVXTwqyUa97NQRfyM6tt',
                'current_team_id' => null,
                'image' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            1 => [
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
                'email_verified_at' => null,
                'password' => '$2y$12$atEjRCnSoCeKnbOCT6a.p.EWTQ7GzU97eInXEyEp0OHnVd6vH4Dnm',
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
                'remember_token' => 'jUTwsATaUHosKT5wdUqnmQwBAXdoFtW9ikzZEuJB2Sd1o2X5Y9rAOhACW5We',
                'current_team_id' => null,
                'image' => 'uploads/users/khairul-islam-emon1748759077683bf225059ea.jpg',
                'created_at' => null,
                'updated_at' => '2026-02-06 11:55:00',
            ],
            2 => [
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
                'email_verified_at' => null,
                'password' => '$2y$12$atEjRCnSoCeKnbOCT6a.p.EWTQ7GzU97eInXEyEp0OHnVd6vH4Dnm',
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
                'remember_token' => null,
                'current_team_id' => null,
                'image' => 'uploads/users/khairul-islam-emon1748759077683bf225059ea.jpg',
                'created_at' => null,
                'updated_at' => '2026-02-09 08:28:34',
            ],
        ]);

    }
}
