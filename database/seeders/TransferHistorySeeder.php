<?php

namespace Database\Seeders;

use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransferHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $service = Service::find(1);
        for ($i = 0; $i < 10; $i++) {
            DB::table('transfer_history')->insert([
                'id' => random_int(100000000, 999999999),
                'seller_id' => $service->seller_id,
                'transfer_amount' => 1000,
                'status' => random_int(0,1),
                'transfer_fee' => 250,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
