<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceDeliverySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $services = Service::all();
        foreach($services as $service){
            if($service->service_type_id == 1){
                DB::table('service_deliveries')->insert([
                    "service_id" => $service->id,
                    "interval" => 0,
                    "month_delivery" => null,
                    "skip" => 0,
                    "with_skip" => null,
                ]);
            }
        }
    }
}
