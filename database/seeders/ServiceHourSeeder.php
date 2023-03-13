<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceHourSeeder extends Seeder
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
                for($i = 0; $i <=6 ; $i++){
                    DB::table('service_hours')->insert(
                        [
                            'service_id' => $service->id,
                            'day_of_week' => $i,
                            'work_hour' => '[{"start":"9:00","end":"22:00"}]',
                            'status' => 1,
                        ]
                    );
                }
            }
        }

    }
}
