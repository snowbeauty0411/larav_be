<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ServiceStoreBuyerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // for ($i = 1; $i <= 51; $i++) {
        //     $ran_year = mt_rand(2019,2022);
        //     $ran_month = mt_rand(1,12);
        //     $ran_date = mt_rand(1,28);
        //     $ran_date_time = $ran_year .'-'.$ran_month.'-'.$ran_date;
        //     DB::table('service_store_buyers')->insert([
        //         "course_id" => "C" . $i,
        //         "buyer_id" => $i,
        //         "qrUrl" => "https://qr.com",
        //         "count" => 1,
        //         "status" => rand(0, 2),
        //         "buy_at" =>   $ran_date_time ,
        //         "cancel_at" => $ran_date_time,
        //         'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
        //         'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        //     ]);
        //     for ($j = 2; $j <= 10; $j++) {
        //         DB::table('service_store_buyers')->insert([
        //             "course_id" => "A" . $j,
        //             "buyer_id" => $i,
        //             "qrUrl" => "https://qr.com",
        //             "count" => 1,
        //             "status" => rand(0, 2),
        //             "buy_at" =>   $ran_date_time ,
        //             "cancel_at" => $ran_date_time,
        //             'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
        //             'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        //         ]);
        //     }
        // }
    }
}
