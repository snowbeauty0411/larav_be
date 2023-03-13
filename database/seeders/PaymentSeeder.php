<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
        DB::table('payments')->insert([
            'service_store_buyer_id'=>1,
            'sub_total' => 1000,
            'service_fee' => 100,
            'total' => 3000,
            'stripe_charge_id' => mt_rand(10000000, 99999999),
            'pay_expire_at_date'=>'2022-01-31',
            'delivery_id'=>1,
            'payment_status'=>mt_rand(0,1)
        ]);

        DB::table('payments')->insert([
            'service_store_buyer_id'=>1,
            'sub_total' => 1000,
            'service_fee' => 100,
            'total' => 25000,
            'stripe_charge_id' => mt_rand(10000000, 99999999),
            'pay_expire_at_date'=>'2022-02-28',
            'delivery_id'=>2,
            'payment_status'=>mt_rand(0,1)
        ]);

        DB::table('payments')->insert([
            'service_store_buyer_id'=>1,
            'sub_total' => 13000,
            'service_fee' => 100,
            'total' => 1100,
            'stripe_charge_id' => mt_rand(10000000, 99999999),
            'pay_expire_at_date'=>'2022-02-15',
            'delivery_id'=>3,
            'payment_status'=>mt_rand(0,1)
        ]);

        DB::table('payments')->insert([
            'service_store_buyer_id'=>1,
            'sub_total' => 1000,
            'service_fee' => 100,
            'total' => 11300,
            'stripe_charge_id' => mt_rand(10000000, 99999999),
            'pay_expire_at_date'=>'2022-03-15',
            'delivery_id'=>4,
            'payment_status'=>mt_rand(0,1)
        ]);

        DB::table('payments')->insert([
            'service_store_buyer_id'=>1,
            'sub_total' => 1000,
            'service_fee' => 100,
            'total' => 24100,
            'stripe_charge_id' => mt_rand(10000000, 99999999),
            'pay_expire_at_date'=>'2022-04-30',
            'delivery_id'=>5,
            'payment_status'=>mt_rand(0,1)
        ]);

        DB::table('payments')->insert([
            'service_store_buyer_id'=>1,
            'sub_total' => 1000,
            'service_fee' => 100,
            'total' => 44000,
            'stripe_charge_id' => mt_rand(10000000, 99999999),
            'pay_expire_at_date'=>'2022-05-31',
            'delivery_id'=>6,
            'payment_status'=>mt_rand(0,1)
        ]);

        DB::table('payments')->insert([
            'service_store_buyer_id'=>1,
            'sub_total' => 1000,
            'service_fee' => 100,
            'total' => 22000,
            'stripe_charge_id' => mt_rand(10000000, 99999999),
            'pay_expire_at_date'=>'2022-06-30',
            'delivery_id'=>7,
            'payment_status'=>mt_rand(0,1)
        ]);

        DB::table('payments')->insert([
            'service_store_buyer_id'=>1,
            'sub_total' => 1000,
            'service_fee' => 100,
            'total' => 9000,
            'stripe_charge_id' => mt_rand(10000000, 99999999),
            'pay_expire_at_date'=>'2022-07-31',
            'delivery_id'=>8,
            'payment_status'=>mt_rand(0,1)
        ]);

        DB::table('payments')->insert([
            'service_store_buyer_id'=>1,
            'sub_total' => 4000,
            'service_fee' => 100,
            'total' => 1100,
            'stripe_charge_id' => mt_rand(10000000, 99999999),
            'pay_expire_at_date'=>'2022-10-15',
            'delivery_id'=>9,
            'payment_status'=>mt_rand(0,1)
        ]);

        DB::table('payments')->insert([
            'service_store_buyer_id'=>1,
            'sub_total' => 1900,
            'service_fee' => 100,
            'total' => 1100,
            'stripe_charge_id' => mt_rand(10000000, 99999999),
            'pay_expire_at_date'=>'2022-10-31',
            'delivery_id'=>10,
            'payment_status'=>mt_rand(0,1)
        ]);

        DB::table('payments')->insert([
            'service_store_buyer_id'=>1,
            'sub_total' => 1000,
            'service_fee' => 100,
            'total' => 190000,
            'stripe_charge_id' => mt_rand(10000000, 99999999),
            'pay_expire_at_date'=>'2022-11-30',
            'delivery_id'=>11,
            'payment_status'=>mt_rand(0,1)
        ]);

        DB::table('payments')->insert([
            'service_store_buyer_id'=>2,
            'sub_total' => 1000,
            'service_fee' => 100,
            'total' => 2800 ,
            'stripe_charge_id' => mt_rand(10000000, 99999999),
            'pay_expire_at_date'=>'2022-01-31',
            'delivery_id'=>12,
            'payment_status'=>mt_rand(0,1)
        ]);

        DB::table('payments')->insert([
            'service_store_buyer_id'=>2,
            'sub_total' => 1000,
            'service_fee' => 100,
            'total' => 68000   ,
            'stripe_charge_id' => mt_rand(10000000, 99999999),
            'pay_expire_at_date'=>'2022-02-28',
            'delivery_id'=>13,
            'payment_status'=>mt_rand(0,1)
        ]);

        DB::table('payments')->insert([
            'service_store_buyer_id'=>2,
            'sub_total' => 1000,
            'service_fee' => 100,
            'total' => 4000,
            'stripe_charge_id' => mt_rand(10000000, 99999999),
            'pay_expire_at_date'=>'2022-02-15',
            'delivery_id'=>14,
            'payment_status'=>mt_rand(0,1)
        ]);

        DB::table('payments')->insert([
            'service_store_buyer_id'=>2,
            'sub_total' => 1000,
            'service_fee'=> 100,
            'total' => 12000,
            'stripe_charge_id' => mt_rand(10000000, 99999999),
            'pay_expire_at_date'=>'2022-03-15',
            'delivery_id'=>15,
            'payment_status'=>mt_rand(0,1)
        ]);

        DB::table('payments')->insert([
            'service_store_buyer_id'=>2,
            'sub_total' => 1000,
            'service_fee' => 100,
            'total' => 27800,
            'stripe_charge_id' => mt_rand(10000000, 99999999),
            'pay_expire_at_date'=>'2022-04-30',
            'delivery_id'=>16,
            'payment_status'=>mt_rand(0,1)
        ]);

        DB::table('payments')->insert([
            'service_store_buyer_id'=>2,
            'sub_total' => 1000,
            'service_fee' => 100,
            'total' => 320000,
            'stripe_charge_id' => mt_rand(10000000, 99999999),
            'pay_expire_at_date'=>'2022-05-31',
            'delivery_id'=>17,
            'payment_status'=>mt_rand(0,1)
        ]);

        DB::table('payments')->insert([
            'service_store_buyer_id'=>2,
            'sub_total' => 1000,
            'service_fee' => 100,
            'total' => 99000,
            'stripe_charge_id' => mt_rand(10000000, 99999999),
            'pay_expire_at_date'=>'2022-06-30',
            'delivery_id'=>18,
            'payment_status'=>mt_rand(0,1)
        ]);

        DB::table('payments')->insert([
            'service_store_buyer_id'=>2,
            'sub_total' => 1000,
            'service_fee' => 100,
            'total' => 1100,
            'stripe_charge_id' => mt_rand(10000000, 99999999),
            'pay_expire_at_date'=>'2022-07-31',
            'delivery_id'=>19,
            'payment_status'=>mt_rand(0,1)
        ]);

        DB::table('payments')->insert([
            'service_store_buyer_id'=>2,
            'sub_total' => 1000,
            'service_fee' => 100,
            'total' => 2000,
            'stripe_charge_id' => mt_rand(10000000, 99999999),
            'pay_expire_at_date'=>'2022-10-15',
            'delivery_id'=>20,
            'payment_status'=>mt_rand(0,1)
        ]);

        DB::table('payments')->insert([
            'service_store_buyer_id'=>2,
            'sub_total' => 1000,
            'service_fee' => 100,
            'total' => 32900,
            'stripe_charge_id' => mt_rand(10000000, 99999999),
            'pay_expire_at_date'=>'2022-10-31',
            'delivery_id'=>21,
            'payment_status'=>mt_rand(0,1)
        ]);

        DB::table('payments')->insert([
            'service_store_buyer_id'=>3,
            'sub_total' => 1000,
            'service_fee' => 100,
            'total' => 210000,
            'stripe_charge_id' => mt_rand(10000000, 99999999),
            'pay_expire_at_date'=>'2022-11-30',
            'delivery_id'=>22,
            'payment_status'=>mt_rand(0,1)
        ]);
        */
    }
}
