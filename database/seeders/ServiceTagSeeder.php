<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 1; $i <= 10; $i++) {
            DB::table('service_tags')->insert([
                'service_id' => $i,
                'tag_id' => 1
            ]);

            DB::table('service_tags')->insert([
                'service_id' => $i,
                'tag_id' => 2
            ]);

            DB::table('service_tags')->insert([
                'service_id' => $i,
                'tag_id' => 3
            ]);
        }
    }
}
