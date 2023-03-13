<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SellerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 1; $i <= 53; $i++) {
            DB::table('sellers')->insert([
                'account_id' => $i,
                'account_name' => 'seller'.$i,
                'business_id' => 1,
                'url_official_id' => mt_rand(1,3),
                'profile_text_sell' => 'ここに説明文が入ります。ここに説明文が入ります。ここに説明文が入ります。ここに説明文が入ります。ここに説明文が入ります。ここに説明文が入ります。ここに説明文が入ります。ここに説明文が入ります。ここに説明文が入ります。ここに説明文が入ります。ここに説明文が入ります。ここに説明文が入ります。'
            ]);
        }
    }
}
