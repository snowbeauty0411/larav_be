<?php

namespace Database\Seeders;

use App\Models\Prefecture;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceAreaSeeder extends Seeder
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
            $area_id = rand(1, 8);
            $pre = Prefecture::where('area_id', $area_id)->get()->pluck('id')->toArray();
            DB::table('service_areas')->insert([
                'service_id' => $service,
                'area_id' => $area_id,
                'pref_id' => rand($pre[0], $pre[count($pre) - 1]),
            ]);
        }
    }

}
