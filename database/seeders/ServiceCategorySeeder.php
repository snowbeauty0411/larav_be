<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $list_category = array(
            'コンピューター・Web・IT・AI・通信関連',
            '音楽・ミュージック・芸能',
            '病院・クリニック・歯医者・医療・薬',
            '飲食店・カフェ・食品関連',
            '学校・教育・スクール',
            '美容室・サロン・エステ・ヨガ',
            'ペット・動物・生き物・植物',
            '体験・交流・遊び',
            '建築・建設・不動産・家・庭',
            'ファッション・おしゃれ',
            '家電・生活用品',
            '家具・インテリア',
            '宿泊・旅行',
            '交通・乗り物',
            '書籍・絵本',
            '美容・コスメ・健康',
            '生活関連'
        );

        foreach ($list_category as $name) {
            $category = new ServiceCategory();
            $category['name'] = $name;
            $category->save();
        }
    }
}
