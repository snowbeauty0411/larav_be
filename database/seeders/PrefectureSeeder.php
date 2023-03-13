<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrefectureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sub_areas = [
            [
                'area_id' => 1,
                'name' => '北海道',
            ],
            [
                'area_id' => 2,
                'name' => '青森',
            ],
            [
                'area_id' => 2,
                'name' => '岩手',
            ],
            [
                'area_id' => 2,
                'name' => '宮城',
            ],
            [
                'area_id' => 2,
                'name' => '秋田',
            ],
            [
                'area_id' => 2,
                'name' => '山形',
            ],
            [
                'area_id' => 2,
                'name' => '福島',
            ],
            [
                'area_id' => 3,
                'name' => '東京',
            ],
            [
                'area_id' => 3,
                'name' => '神奈川',
            ],
            [
                'area_id' => 3,
                'name' => '埼玉',
            ],
            [
                'area_id' => 3,
                'name' => '千葉',
            ],
            [
                'area_id' => 3,
                'name' => '栃木',
            ],
            [
                'area_id' => 3,
                'name' => '群馬',
            ],
            [
                'area_id' => 3,
                'name' => '茨城',
            ],
            [
                'area_id' => 4,
                'name' => '愛知',
            ],
            [
                'area_id' => 4,
                'name' => '三重',
            ],
            [
                'area_id' => 4,
                'name' => '静岡',
            ],
            [
                'area_id' => 4,
                'name' => '岐阜',
            ],
            [
                'area_id' => 4,
                'name' => '山梨',
            ],
            [
                'area_id' => 4,
                'name' => '長野',
            ],
            [
                'area_id' => 4,
                'name' => '新潟',
            ],
            [
                'area_id' => 5,
                'name' => '大阪',
            ],
            [
                'area_id' => 5,
                'name' => '京都',
            ],
            [
                'area_id' => 5,
                'name' => '滋賀',
            ],
            [
                'area_id' => 5,
                'name' => '兵庫',
            ],
            [
                'area_id' => 5,
                'name' => '奈良',
            ],
            [
                'area_id' => 5,
                'name' => '和歌山',
            ],
            [
                'area_id' => 6,
                'name' => '岡山',
            ],
            [
                'area_id' => 6,
                'name' => '島根',
            ],
            [
                'area_id' => 6,
                'name' => '鳥取',
            ],
            [
                'area_id' => 6,
                'name' => '広島',
            ],
            [
                'area_id' => 6,
                'name' => '山口',
            ],
            [
                'area_id' => 7,
                'name' => '徳島',
            ],
            [
                'area_id' => 7,
                'name' => '香川',
            ],
            [
                'area_id' => 7,
                'name' => '愛媛',
            ],
            [
                'area_id' => 7,
                'name' => '高知',
            ],
            [
                'area_id' => 8,
                'name' => '福岡',
            ],
            [
                'area_id' => 8,
                'name' => '佐賀',
            ],
            [
                'area_id' => 8,
                'name' => '長崎',
            ],
            [
                'area_id' => 8,
                'name' => '熊本',
            ],
            [
                'area_id' => 8,
                'name' => '大分',
            ],
            [
                'area_id' => 8,
                'name' => '宮崎',
            ],
            [
                'area_id' => 8,
                'name' => '鹿児島',
            ],
            [
                'area_id' => 8,
                'name' => '沖縄',
            ],
        ];
        foreach ($sub_areas as $key => $sub_area){
            DB::table('prefectures')->insert([
                'area_id' => $sub_area['area_id'],
                'name' => $sub_area['name'],
            ]);
        }
    }
}
