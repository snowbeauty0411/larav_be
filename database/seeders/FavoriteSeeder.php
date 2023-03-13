<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 1; $i <= 10; $i++) {
            for ($j = 1; $j <= 50; $j++) {
                $month = mt_rand(1,12);
                $year = mt_rand(2020,2022);
                $date = mt_rand(1, 28);
                DB::table('favorites')->insert([
                    "service_id" => $j,
                    "buyer_id" => $i,
                    "created_at" => $year . '-' . $month . '-' . $date
                ]);
            }
        }
    }
}
