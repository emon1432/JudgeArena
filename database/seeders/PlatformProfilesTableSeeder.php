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
                'last_synced_at' => '2026-07-13 20:02:13',
                'created_at' => '2026-05-31 14:49:48',
                'updated_at' => '2026-07-13 20:02:13',
            ),
            1 => 
            array (
                'id' => 2,
                'user_id' => 2,
                'platform_id' => 1,
                'handle' => 'tourist',
                'raw' => '{"handle":"tourist","email":null,"vkId":null,"openId":null,"firstName":"Gennady","lastName":"Korotkevich","country":"Belarus","city":"Gomel","organization":"ITMO University","contribution":109,"rank":"legendary grandmaster","rating":3530,"maxRank":"tourist","maxRating":4009,"lastOnlineTimeSeconds":1786731733,"registrationTimeSeconds":1265987288,"friendOfCount":90294,"avatar":"https:\\/\\/userpic.codeforces.org\\/422\\/avatar\\/2b5dbe87f0d859a2.jpg","titlePhoto":"https:\\/\\/userpic.codeforces.org\\/422\\/title\\/50a270ed4a722867.jpg"}',
                'metadata' => '{"source":"user-import","platform":"codeforces","handle":"tourist","synced_at":"2026-08-14T18:59:02.024778Z"}',
                'status' => 'Active',
                'last_synced_at' => '2026-08-14 18:59:02',
                'created_at' => '2026-05-31 14:49:48',
                'updated_at' => '2026-08-14 18:59:02',
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
                'raw' => '{"handle":"emon_mon","email":"e.mon143298@gmail.com","vkId":null,"openId":null,"firstName":"Khairul Islam","lastName":"Emon","country":"Bangladesh","city":"Dhaka","organization":"Institute of Science and Technology","contribution":0,"rank":"newbie","rating":986,"maxRank":"pupil","maxRating":1389,"lastOnlineTimeSeconds":1786717021,"registrationTimeSeconds":1568830934,"friendOfCount":14,"avatar":"https:\\/\\/userpic.codeforces.org\\/1251151\\/avatar\\/831bc82d1bd1a504.jpg","titlePhoto":"https:\\/\\/userpic.codeforces.org\\/1251151\\/title\\/bb229b5b047bfc07.jpg"}',
                'metadata' => '{"source":"user-import","platform":"codeforces","handle":"emon_mon","synced_at":"2026-08-14T18:59:02.020979Z"}',
                'status' => 'Active',
                'last_synced_at' => '2026-08-14 18:59:02',
                'created_at' => '2026-06-01 12:40:13',
                'updated_at' => '2026-08-14 18:59:02',
            ),
        ));
        
        
    }
}