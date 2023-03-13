<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UrlOfficialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('url_officials')->insert([
            'id' => 1,
            'url_official' => 'https://www.example.com/',
            'url_facebook' => 'https://www.facebook.com/',
            'url_instagram' => 'https://www.instagram.com/',
            'url_twitter' => 'https://twitter.com/',
            'url_sns_1' => 'https://example.com',
            'url_sns_2' => 'https://example.com',
        ]);

        DB::table('url_officials')->insert([
            'id' => 2,
            'url_official' => 'https://www.example.com/',
            'url_facebook' => 'https://www.facebook.com/',
            'url_instagram' => 'https://www.instagram.com/',
            'url_twitter' => 'https://twitter.com/',
            'url_sns_1' => 'https://example.com',
            'url_sns_2' => 'https://example.com',
        ]);

        DB::table('url_officials')->insert([
            'id' => 3,
            'url_official' => 'https://www.example.com/',
            'url_facebook' => 'https://www.facebook.com/',
            'url_instagram' => 'https://www.instagram.com/',
            'url_twitter' => 'https://twitter.com/',
            'url_sns_1' => 'https://example.com',
            'url_sns_2' => 'https://example.com',
        ]);
    }
}
