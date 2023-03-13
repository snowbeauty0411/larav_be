<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceCourseImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_course_images', function (Blueprint $table) {
            $table->increments('id');
            $table->char('course_id', 255);
            $table->string('image_url');
            $table->timestamps();
            $table->foreign('course_id')->cascadeOnUpdate()->references('course_id')->on('service_courses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_course_images');
    }
}
