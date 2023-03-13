<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class ServiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('service_types')->insert([
            'name' => '定期便タイプ',
        ]);
        DB::table('service_types')->insert([
            'name' => '店舗型タイプ',
        ]);
        DB::table('service_types')->insert([
            'name' => '訪問型タイプ/オンライン完結タイプ',
        ]);
        DB::table('service_types')->insert([
            'name' => '外部リンクタイプ',
        ]);
        DB::table('service_types')->insert([
            'name' => 'どれにも当てはまらない',
        ]);
    }
}
