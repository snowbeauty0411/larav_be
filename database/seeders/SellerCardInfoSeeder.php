<?php

namespace Database\Seeders;

use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SellerCardInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $service = Service::find(1);
        DB::table('seller_card_info')->insert([
            'id' => $service->seller_id,
            'financial_institution_name' => 'Yucho Ginko',
            'account_number' => '123456789',
            'account_holder' => 'Seller',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
