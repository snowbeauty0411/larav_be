<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceStepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $services = Service::all()->pluck('id')->toArray();
        foreach($services as $service){
            DB::table('service_steps')->insert([
                "service_id" => $service,
                "number" => 1,
                "title" => "step1",
            ]);
        }
    }
}
