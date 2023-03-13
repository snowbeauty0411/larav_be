<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NumberAccessListServicePageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   
        DB::table('number_access_list_service_pages')->truncate();
        $current_month=Carbon::now()->month;
        for ($j = 1; $j <= 10; $j++) {
            for ($i = 1; $i <= $current_month; $i++) {
                $month = $i;
                $year = Carbon::now()->year;
                $date = mt_rand(1, 28);
                DB::table('number_access_list_service_pages')->insert([
                    'service_id' => $j,
                    'count_by_month' => mt_rand(100, 900),
                    'created_at' => $year . '-' . $month . '-' . $date
                ]);
            }

            for ($i = 1; $i <= 12; $i++) {
                $month = $i;
                $year = 2021;
                $date = mt_rand(1, 28);
                DB::table('number_access_list_service_pages')->insert([
                    'service_id' => $j,
                    'count_by_month' => mt_rand(100, 900),
                    'created_at' => $year . '-' . $month . '-' . $date
                ]);
            }

            for ($i = 1; $i <= 12; $i++) {
                $month = $i;
                $year = 2020;
                $date = mt_rand(1, 28);
                DB::table('number_access_list_service_pages')->insert([
                    'service_id' => $j,
                    'count_by_month' => mt_rand(100, 900),
                    'created_at' => $year . '-' . $month . '-' . $date
                ]);
            }
        }
    }
}
