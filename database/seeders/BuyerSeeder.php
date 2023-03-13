<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Buyer;

class BuyerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 1; $i <= 53; $i++) {
            DB::table('buyers')->insert([
                'account_id' => $i,
                'account_name' => 'buyer'.$i,
                'url_official_id' => mt_rand(1,3),
                'profile_text_buy' => 'ここに説明文が入ります。ここに説明文が入ります。ここに説明文が入ります。ここに説明文が入ります。ここに説明文が入ります。'
            ]);
        }
    }
}
