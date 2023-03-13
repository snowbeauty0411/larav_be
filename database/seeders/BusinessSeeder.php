<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('businesses')->insert([
            'business_name' => '個人',
        ]);
        DB::table('businesses')->insert([
            'business_name' => '法人',
        ]);
    }
}
