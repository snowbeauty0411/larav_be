<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
   
            for ($i = 5; $i <= 51; $i++) {
                for($j=1;$j<=112;$j++){
                    $year = mt_rand(2020, 2022);
                    $month = mt_rand(1,12);
                    $date = mt_rand(1, 28);
                    DB::table('service_reviews')->insert([
                        'buyer_id' => $i,
                        'service_id' => $j,
                        'description' => 'review service example' . mt_rand(1, 200),
                        'rating' => mt_rand(1, 5),
                        'seller_reply' => '',
                        'created_at' => $year . '-' . $month . '-' . $date
                    ]);
                }

            }
    }
}
