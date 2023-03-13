<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceReserveSettingSeeder extends Seeder
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
            if($service->service_type_id == 2 || $service->service_type_id == 3){
                DB::table('service_reserves_setting')->insert(
                    [
                        'service_id' => $service->id,
                        'max' => 1,
                        'time_distance' => "1:00",
                        'duration_before' => 60,
                        'duration_after' => 1,
                    ]
                );
            }
        }
    }
}
