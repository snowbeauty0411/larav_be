<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      for($i=1;$i<=20;$i++){
        DB::table('contacts')->insert([
            "name"=>"user_name_".$i,
            "email"=>"example".$i."@yopmail.com",
            "content"=>"example content 1"
        ]);
      }
    }
}
