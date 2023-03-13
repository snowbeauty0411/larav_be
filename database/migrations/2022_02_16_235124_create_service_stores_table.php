<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_stores', function (Blueprint $table) {
            $table->increments('id');
            $table->char('course_id', 255)->unique();
            $table->integer('count');
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
        Schema::dropIfExists('service_stores');
    }
}
