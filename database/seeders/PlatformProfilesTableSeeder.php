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
            'raw' => '{"username":"tourist","avatarUrl":"https:\\/\\/img.atcoder.jp\\/icons\\/267f5de4d8768543b1570f07e47b5316.jpg","country":"Belarus","birthYear":"1994","twitterId":"@que_tourist","topcoderId":"tourist","codeforcesId":"tourist","affiliation":"ITMO University","contestStatus":{"algo":{"rank":"1st (Top <0.01%)","rating":"3797","highest_rating":"4229\\n\\t\\t\\t\\t\\t\\t\\t\\u2015\\n\\t\\t\\t\\t\\t\\t\\tKing\\n\\t\\t\\t\\t\\t\\t\\t\\n\\t\\t\\t\\t\\t\\t\\t\\t(+171 to promote)","rated_matches":"71","last_competed":"2026\\/03\\/29"},"heuristic":{"rank":"304th (Top 4.54%)","rating":"2085\\n\\t\\t\\t\\t\\t\\t(Provisional)","highest_rating":"2383","rated_matches":"5","last_competed":"2024\\/07\\/21"}}}',
                'metadata' => '{"source":"user-import","platform":"atcoder","handle":"tourist","synced_at":"2026-07-13T20:02:13.037301Z"}',
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
                'raw' => '{"handle":"tourist","email":null,"vkId":null,"openId":null,"firstName":"Gennady","lastName":"Korotkevich","country":"Belarus","city":"Gomel","organization":"ITMO University","contribution":55,"rank":"legendary grandmaster","rating":3439,"maxRank":"tourist","maxRating":4009,"lastOnlineTimeSeconds":1783947300,"registrationTimeSeconds":1265987288,"friendOfCount":89653,"avatar":"https:\\/\\/userpic.codeforces.org\\/422\\/avatar\\/2b5dbe87f0d859a2.jpg","titlePhoto":"https:\\/\\/userpic.codeforces.org\\/422\\/title\\/50a270ed4a722867.jpg"}',
                'metadata' => '{"source":"user-import","platform":"codeforces","handle":"tourist","synced_at":"2026-07-13T20:02:11.061996Z"}',
                'status' => 'Active',
                'last_synced_at' => '2026-07-13 20:02:11',
                'created_at' => '2026-05-31 14:49:48',
                'updated_at' => '2026-07-13 20:02:11',
            ),
            2 => 
            array (
                'id' => 3,
                'user_id' => 3,
                'platform_id' => 2,
                'handle' => 'e_mon',
                'raw' => '{"username":"e_mon","avatarUrl":"\\/\\/img.atcoder.jp\\/assets\\/icon\\/avatar.png","country":"Bangladesh","birthYear":"1998","twitterId":null,"topcoderId":null,"codeforcesId":null,"affiliation":null,"contestStatus":{"algo":{"rank":null,"rating":null,"highest_rating":null,"rated_matches":null,"last_competed":null},"heuristic":{"rank":null,"rating":null,"highest_rating":null,"rated_matches":null,"last_competed":null}}}',
                'metadata' => '{"source":"user-import","platform":"atcoder","handle":"e_mon","synced_at":"2026-07-13T20:02:12.199585Z"}',
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
                'raw' => '{"handle":"emon_mon","email":"e.mon143298@gmail.com","vkId":null,"openId":null,"firstName":"Khairul Islam","lastName":"Emon","country":"Bangladesh","city":"Dhaka","organization":"Institute of Science and Technology","contribution":0,"rank":"newbie","rating":986,"maxRank":"pupil","maxRating":1389,"lastOnlineTimeSeconds":1783959670,"registrationTimeSeconds":1568830934,"friendOfCount":14,"avatar":"https:\\/\\/userpic.codeforces.org\\/1251151\\/avatar\\/831bc82d1bd1a504.jpg","titlePhoto":"https:\\/\\/userpic.codeforces.org\\/1251151\\/title\\/bb229b5b047bfc07.jpg"}',
                'metadata' => '{"source":"user-import","platform":"codeforces","handle":"emon_mon","synced_at":"2026-07-13T20:02:10.245994Z"}',
                'status' => 'Active',
                'last_synced_at' => '2026-07-13 20:02:10',
                'created_at' => '2026-06-01 12:40:13',
                'updated_at' => '2026-07-13 20:02:10',
            ),
        ));
        
        
    }
}