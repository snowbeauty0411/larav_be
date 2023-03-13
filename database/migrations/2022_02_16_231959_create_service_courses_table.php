<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_courses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_id');
            $table->char('course_id', 255)->unique();
            $table->string('name');
            $table->integer('price')->unsigned()->nullable();
            $table->integer('cycle')->unsigned()->nullable();
            $table->integer('age_confirm')->unsigned()->nullable(); // 0 - 不要; >= 1 - 必要
            $table->integer('gender_restrictions')->nullable();  // 0 - 不要; 1 - 女性のみ利用可能; 2 - 男性のみ利用可能
            $table->text('content')->nullable();
            // $table->integer('max')->unsigned()->nullable();
            $table->boolean('firstPr')->nullable(); // false - しない; true - する
            $table->timestamps();
            $table->foreign('service_id')->cascadeOnUpdate()->references('id')->on('services');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_courses');
    }
}
