<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MessageThreadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('message_threads')->insert([
            'seller_id' => 1,
            'buyer_id' => 2,
        ]);

        DB::table('message_threads')->insert([
            'seller_id' => 1,
            'buyer_id' => 4,
        ]);

        DB::table('message_threads')->insert([
            'seller_id' => 1,
            'buyer_id' => 5,
        ]);

        DB::table('message_threads')->insert([
            'seller_id' => 1,
            'buyer_id' => 6,
        ]);

        DB::table('message_threads')->insert([
            'seller_id' => 1,
            'admin_id' => 1,
        ]);

        DB::table('message_threads')->insert([
            'seller_id' => 2,
            'buyer_id' => 1,
        ]);

        DB::table('message_threads')->insert([
            'seller_id' => 2,
            'buyer_id' => 3,
        ]);

        
        DB::table('message_threads')->insert([
            'seller_id' => 2,
            'buyer_id' => 4,
        ]);

        DB::table('message_threads')->insert([
            'seller_id' => 2,
            'buyer_id' => 5,
        ]);

        DB::table('message_threads')->insert([
            'seller_id' => 2,
            'admin_id' => 1,
        ]);

        DB::table('message_threads')->insert([
            'buyer_id' => 1,
            'admin_id' => 1,
        ]);

        DB::table('message_threads')->insert([
            'buyer_id' => 2,
            'admin_id' => 1,
        ]);
    }
}
