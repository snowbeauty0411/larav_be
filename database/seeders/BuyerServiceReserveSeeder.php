<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BuyerServiceReserveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // for($i = 1; $i <= 50; $i++){
        //     DB::table('buyer_service_reserves')->insert(
        //         [
        //             'buyer_id' => $i,
        //             'service_id' => 1,
        //             'course_id' => 'A1',
        //             'reserve_start' => Carbon::now()->format('Y-m-d') . ' 09:00:00',
        //             'reserve_end' => Carbon::now()->format('Y-m-d') . ' 10:00:00',
        //         ]
        //     );
        //     DB::table('buyer_service_reserves')->insert(
        //         [
        //             'buyer_id' => $i,
        //             'service_id' => 2,
        //             'course_id' => 'A2',
        //             'reserve_start' => Carbon::now()->format('Y-m-d') . ' 18:00:00',
        //             'reserve_end' => Carbon::now()->format('Y-m-d') . ' 19:00:00',
        //         ]
        //     );
        //     DB::table('buyer_service_reserves')->insert(
        //         [
        //             'buyer_id' => $i,
        //             'service_id' => 6,
        //             'course_id' => 'A6',
        //             'reserve_start' => Carbon::now()->format('Y-m-d') . ' 21:00:00',
        //             'reserve_end' => Carbon::now()->format('Y-m-d') . ' 22:00:00',
        //         ]
        //     );
        //     DB::table('buyer_service_reserves')->insert(
        //         [
        //             'buyer_id' => $i,
        //             'service_id' => 3,
        //             'course_id' => 'A3',
        //             'reserve_start' => Carbon::now()->addDays(random_int(1,90))->format('Y-m-d') . ' 10:00:00',
        //             'reserve_end' => Carbon::now()->addDays(random_int(1,90))->format('Y-m-d') . ' 14:00:00',
        //         ]
        //     );
        //     DB::table('buyer_service_reserves')->insert(
        //         [
        //             'buyer_id' => $i,
        //             'service_id' => 4,
        //             'course_id' => 'A4',
        //             'reserve_start' => Carbon::now()->addDays(random_int(1,90))->format('Y-m-d') . ' 09:00:00',
        //             'reserve_end' => Carbon::now()->addDays(random_int(1,90))->format('Y-m-d') . ' 11:00:00',
        //         ]
        //     );
        //     DB::table('buyer_service_reserves')->insert(
        //         [
        //             'buyer_id' => $i,
        //             'service_id' => 5,
        //             'course_id' => 'A5',
        //             'reserve_start' => Carbon::now()->addDays(random_int(1,90))->format('Y-m-d') . ' 11:00:00',
        //             'reserve_end' => Carbon::now()->addDays(random_int(1,90))->format('Y-m-d') . ' 12:00:00',
        //         ]
        //     );
        // }
    }
}
