<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tags')->insert([
            "name" => "飲食",
        ]);
        DB::table('tags')->insert([
            "name" => "料理",
        ]);
        DB::table('tags')->insert([
            "name" => "イタリアン",
        ]);
    }
}
