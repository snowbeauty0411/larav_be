<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 1; $i <= 51; $i++) {
            DB::table('service_courses')->insert([
                "service_id" => 1,
                "course_id" => "C" . $i,
                "name" => "service_1_courses_name" . $i,
                "price" => 1000 * $i,
                "cycle" => random_int(1,3),
                "content" => "・BASE BREAD プレーン、チョコレート、メープル、シナモン客２食（4袋）
                ・BASE Cookies ココア、アールグレイ、客１食（4袋）
                ＋BASE Cookies アールグレイ１袋無料プレゼント
                ＋BASE Cookies アールグレイ１袋無料プレゼント
                ＋BASE Cookies アールグレイ１袋無料プレゼント",
                // "max" => 100,
                "firstPr" => random_int(0,1)
            ]);
            DB::table('service_course_images')->insert([
                "course_id" => "C" . $i,
                "image_url" => "images/service_courses/imgDefault.png",
            ]);
        }
    }
}
