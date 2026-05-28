<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PlatformsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('platforms')->delete();

        \DB::table('platforms')->insert(array(
            0 =>
            array(
                'id' => 1,
                'name' => 'Codeforces',
                'slug' => 'codeforces',
                'short_name' => 'CF',
                'base_url' => 'https://codeforces.com/',
                'icon' => 'uploads/platforms/Codeforces17798750086a16bcc08d73d.png',
                'description' => 'Codeforces is a competitive programming platform that hosts regular contests and provides a wide range of problems for programmers to solve.',
                'settings' => '{"base_url":"https://codeforces.com/","api_base_url":"https://codeforces.com/api/","api_key":"6030d6fd331a0197c6528a03bad601d0933eaf14","api_secret":"2d2eb2f9b3ae5c65d40eb4a84d1fbc5a50ef5a7e"}',
                'status' => 'Active',
                'created_at' => '2026-05-27 09:43:28',
                'updated_at' => '2026-05-27 09:43:28',
            ),
            1 =>
            array(
                'id' => 2,
                'name' => 'AtCoder',
                'slug' => 'atcoder',
                'short_name' => 'AC',
                'base_url' => 'https://atcoder.jp/',
                'icon' => 'uploads/platforms/AtCoder17798753066a16bdea08f07.png',
                'description' => 'AtCoder is a Japanese online judge hosting regular contests and providing educational problems for competitive programmers.',
                'settings' => '{"base_url":"https://atcoder.jp/","username":"e_mon","password":"Lovemon1432","session_cookies":"REVEL_SESSION=8f32956147b47a3ed94f28b08a476241358aee52-%00UserScreenName%3Ae_mon%00%00UserName%3Ae_mon%00%00a%3Afalse%00%00w%3Afalse%00%00csrf_token%3AShVdCL18ATqUzZsWLczPC1d5ruMXEHJb8zY3TDXngu0%3D%00%00_TS%3A1795285626%00%00SessionKey%3A72662f2757487fb538c4e9ffea478da84f4a6a2914f5ae099ebbbfa292f16d5d%00"}',
                'status' => 'Active',
                'created_at' => '2026-05-27 09:48:26',
                'updated_at' => '2026-05-27 09:48:26',
            ),
        ));
    }
}
