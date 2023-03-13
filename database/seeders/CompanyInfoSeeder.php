<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('company_info')->insert([
            'name'=>'株式会社スタッコ (旧エバーファースト)',
            'address'=>'〒:615-0884 京都府京都市右京区西京極郡町 96-2',
            'tel'=>'075-963-6161',
            'fax'=>'075-963-6162',
            'establish'=>'平成17年11月',
            'capital'=>'10,000,000円',
            'customer_banks'=>'みずほ銀行船場支店 
京都信用金庫梅津支店 ',
            'ceo'=>'甘竹繁人',
            'director'=>'金子政右',
            'website_url'=>'http://www.stuccoplus.co.jp/',
            'business_content'=>'建築資材の輸入、販売 建築資材の新製品の開発、販売 壁画の設計デザイン･制作施工 特殊塗装及び特殊左官工事の施工 経営理念 
            安全で、環境に優しく、意匠性豊かな建材で地球と人間社会に貢献する',
            'other'=>'建設業許可　塗装工事業　京都府知
            事　般-28第39176号
            京都商工会議所会員 
            グリーンサイト・CCUS加入'
        ]);
    }
}
