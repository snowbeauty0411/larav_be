<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliverySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
        for ($i = 1; $i <= 5; $i++) {
            $order = DB::table('service_store_buyers')->where('id',$i)->first();
            $buyer_id=$order->buyer_id;
            $shipping_info_buyer = DB::table('shipping_info')->where('buyer_id',$buyer_id)->first();

            DB::table('deliveries')->insert([
                'service_store_buyer_id'=>$i,
                'estimated_date' =>  '2022-01-15',
                'delivery_status' => mt_rand(0, 2),
                'delivery_address'=>$shipping_info_buyer->post_code.' '.$shipping_info_buyer->address,
                'buyer_full_name'=>$shipping_info_buyer->first_name.' '.$shipping_info_buyer->last_name
            ]);

            DB::table('deliveries')->insert([
                'service_store_buyer_id'=>$i,
                'estimated_date' =>  '2022-02-15',
                'delivery_status' => mt_rand(0, 2),
                'delivery_address'=>$shipping_info_buyer->post_code.' '.$shipping_info_buyer->address,
                'buyer_full_name'=>$shipping_info_buyer->first_name.' '.$shipping_info_buyer->last_name
            ]);

            DB::table('deliveries')->insert([
                'service_store_buyer_id'=>$i,
                'estimated_date' =>  '2022-03-15',
                'delivery_status' => mt_rand(0, 2),
                'delivery_address'=>$shipping_info_buyer->post_code.' '.$shipping_info_buyer->address,
                'buyer_full_name'=>$shipping_info_buyer->first_name.' '.$shipping_info_buyer->last_name
            ]);

            DB::table('deliveries')->insert([
                'service_store_buyer_id'=>$i,
                'estimated_date' =>  '2022-04-15',
                'delivery_status' => mt_rand(0, 2),
                'delivery_address'=>$shipping_info_buyer->post_code.' '.$shipping_info_buyer->address,
                'buyer_full_name'=>$shipping_info_buyer->first_name.' '.$shipping_info_buyer->last_name
            ]);

            DB::table('deliveries')->insert([
                'service_store_buyer_id'=>$i,
                'estimated_date' =>  '2022-05-15',
                'delivery_status' => mt_rand(0, 2),
                'delivery_address'=>$shipping_info_buyer->post_code.' '.$shipping_info_buyer->address,
                'buyer_full_name'=>$shipping_info_buyer->first_name.' '.$shipping_info_buyer->last_name
            ]);


            DB::table('deliveries')->insert([
                'service_store_buyer_id'=>$i,
                'estimated_date' =>  '2022-06-15',
                'delivery_status' => mt_rand(0, 2),
                'delivery_address'=>$shipping_info_buyer->post_code.' '.$shipping_info_buyer->address,
                'buyer_full_name'=>$shipping_info_buyer->first_name.' '.$shipping_info_buyer->last_name
            ]);

            DB::table('deliveries')->insert([
                'service_store_buyer_id'=>$i,
                'estimated_date' =>  '2022-07-15',
                'delivery_status' => mt_rand(0, 2),
                'delivery_address'=>$shipping_info_buyer->post_code.' '.$shipping_info_buyer->address,
                'buyer_full_name'=>$shipping_info_buyer->first_name.' '.$shipping_info_buyer->last_name
            ]);

            DB::table('deliveries')->insert([
                'service_store_buyer_id'=>$i,
                'estimated_date' => '2022-07-30',
                'delivery_status' => mt_rand(0, 2),
                'delivery_address'=>$shipping_info_buyer->post_code.' '.$shipping_info_buyer->address,
                'buyer_full_name'=>$shipping_info_buyer->first_name.' '.$shipping_info_buyer->last_name      
            ]);

            DB::table('deliveries')->insert([
                'service_store_buyer_id'=>$i,
                'estimated_date' => '2022-10-30',
                'delivery_status' => mt_rand(0, 2),
                'delivery_address'=>$shipping_info_buyer->post_code.' '.$shipping_info_buyer->address,
                'buyer_full_name'=>$shipping_info_buyer->first_name.' '.$shipping_info_buyer->last_name
            ]);

            DB::table('deliveries')->insert([
                'service_store_buyer_id'=>$i,
                'estimated_date' => '2022-11-30',
                'delivery_status' => mt_rand(0, 2),
                'delivery_address'=>$shipping_info_buyer->post_code.' '.$shipping_info_buyer->address,
                'buyer_full_name'=>$shipping_info_buyer->first_name.' '.$shipping_info_buyer->last_name
            ]);

            DB::table('deliveries')->insert([
                'service_store_buyer_id'=>$i,
                'estimated_date' => '2022-12-30',
                'delivery_status' => mt_rand(0, 2),
                'delivery_address'=>$shipping_info_buyer->post_code.' '.$shipping_info_buyer->address,
                'buyer_full_name'=>$shipping_info_buyer->first_name.' '.$shipping_info_buyer->last_name
               
            ]);
        }
        */
    }
}
