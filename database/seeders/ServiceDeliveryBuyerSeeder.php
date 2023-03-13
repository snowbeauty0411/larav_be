<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceDeliveryBuyerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('service_delivery_buyers')->insert([
            "course_id" => "A1",
            "buyer_id" => 2,
            "start" => "2022-01-15 04:28:07",
            "end" => "2022-03-15 04:28:07",
            "address" => "tokyo"
        ]);
    }
}
