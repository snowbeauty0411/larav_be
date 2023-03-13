<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShippingInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('shipping_info')->insert([
      
            'buyer_id' => 1,
            'last_name' => '南野',
            'first_name' => '拓実',
            'last_name_kana' => 'タクミ',
            'first_name_kana' => 'ミナミノ',
            'phone' => '050-5488-6340',
            'post_code' => '105-0013',
            'address' => '東京都港区浜松町1-30-11 浜松町FGビル2F',
        ]);

        DB::table('shipping_info')->insert([
      
            'buyer_id' => 1,
            'last_name' => '南野',
            'first_name' => '拓実',
            'last_name_kana' => 'タクミ',
            'first_name_kana' => 'ミナミノ',
            'phone' => '050-5488-6340',
            'post_code' => '479-0882',
            'address' => '愛知県常滑市りんくう町２丁目20−３',
        ]);

        DB::table('shipping_info')->insert([
      
            'buyer_id' => 1,
            'last_name' => '南野',
            'first_name' => '拓実',
            'last_name_kana' => 'タクミ',
            'first_name_kana' => 'ミナミノ',
            'phone' => '050-5488-6340',
            'post_code' => '479-0838',
            'address' => '愛知県常滑市鯉江本町５丁目',
            'is_default'=>1
        ]);

        DB::table('shipping_info')->insert([
         
            'buyer_id' => 2,
            'last_name' => '宏樹',
            'first_name' => '竹中',
            'last_name_kana' => 'ヒロキ',
            'first_name_kana' => 'タケナカ',
            'phone' => '050-5492-4618',
            'post_code' => '456-0032',
            'address' => '愛知県名古屋市熱田区三本松町１８',
            'is_default'=>1
        ]);

        
        DB::table('shipping_info')->insert([
         
            'buyer_id' => 2,
            'last_name' => '宏樹',
            'first_name' => '竹中',
            'last_name_kana' => 'ヒロキ',
            'first_name_kana' => 'タケナカ',
            'phone' => '050-5492-4618',
            'post_code' => '460-0011',
            'address' => '愛知県名古屋市中区大須２丁目２１−４７',
        ]);

        
        DB::table('shipping_info')->insert([
         
            'buyer_id' => 2,
            'last_name' => '宏樹',
            'first_name' => '竹中',
            'last_name_kana' => 'ヒロキ',
            'first_name_kana' => 'タケナカ',
            'phone' => '050-5492-4618',
            'post_code' => '479-0823',
            'address' => '愛知県常滑市奥栄町１丁目１３０'
        ]);

        DB::table('shipping_info')->insert([
       
            'buyer_id' => 3,
            'last_name' => 'ゆあ',
            'first_name' => '長崎',
            'last_name_kana' => 'ユア',
            'first_name_kana' => 'ナガサキ',
            'phone' => '050-5485-9267',
            'post_code' => '160-0020',
            'address' => '東京都新宿区新宿3-13-1 新宿文化ビル3F',
        ]);

        DB::table('shipping_info')->insert([
       
            'buyer_id' => 3,
            'last_name' => 'ゆあ',
            'first_name' => '長崎',
            'last_name_kana' => 'ユア',
            'first_name_kana' => 'ナガサキ',
            'phone' => '050-5485-9267',
            'post_code' => '160-0021',
            'address' => '東京都新宿区新宿3-13-2 新宿文化ビル3F',
        ]);

        DB::table('shipping_info')->insert([
       
            'buyer_id' => 3,
            'last_name' => 'ゆあ',
            'first_name' => '長崎',
            'last_name_kana' => 'ユア',
            'first_name_kana' => 'ナガサキ',
            'phone' => '050-5485-9267',
            'post_code' => '160-0022',
            'address' => '東京都新宿区新宿3-13-3 新宿文化ビル3F',
            'is_default'=>1
        ]);

        DB::table('shipping_info')->insert([
           
            'buyer_id' => 4,
            'last_name' => '太郎',
            'first_name' => '山田',
            'last_name_kana' => 'タロウ',
            'first_name_kana' => 'ヤマダ',
            'phone' => '050-5494-9130',
            'post_code' => '160-0021',
            'address' => '東京都新宿区新宿3-34-10 ピースビル3F',
        ]);

        DB::table('shipping_info')->insert([
           
            'buyer_id' => 4,
            'last_name' => '太郎',
            'first_name' => '山田',
            'last_name_kana' => 'タロウ',
            'first_name_kana' => 'ヤマダ',
            'phone' => '050-5494-9130',
            'post_code' => '160-0022',
            'address' => '東京都新宿区新宿3-34-11 ピースビル3F',
        ]);
        
        DB::table('shipping_info')->insert([
           
            'buyer_id' => 4,
            'last_name' => '太郎',
            'first_name' => '山田',
            'last_name_kana' => 'タロウ',
            'first_name_kana' => 'ヤマダ',
            'phone' => '050-5494-9130',
            'post_code' => '160-0023',
            'address' => '東京都新宿区新宿3-34-12 ピースビル3F',
            'is_default'=>1
        ]);

        DB::table('shipping_info')->insert([
           
            'buyer_id' => 5,
            'last_name' => '吉田',
            'first_name' => '麻也',
            'last_name_kana' => 'ヨシダ',
            'first_name_kana' => 'マヤ',
            'phone' => '0791-22-2341',
            'post_code' => '678-0056',
            'address' => '兵庫県相生市那波東本町6-47',
            'is_default'=>1
        ]);
    }
}
