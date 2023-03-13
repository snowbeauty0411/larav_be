<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $areas = ['北海道','東北','関東','中部', '関西','中国','四国','九州'];
        foreach ($areas as $key => $area){
            DB::table('areas')->insert([
                'name' => $area,
            ]);
        }
    }
}
