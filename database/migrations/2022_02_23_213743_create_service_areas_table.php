<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_areas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_id');
            $table->unsignedInteger('area_id');
            $table->unsignedInteger('pref_id');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->foreign('service_id')->cascadeOnUpdate()->references('id')->on('services');
            $table->foreign('area_id')->cascadeOnUpdate()->references('id')->on('areas');
            $table->foreign('pref_id')->cascadeOnUpdate()->references('id')->on('prefectures');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_areas');
    }
}
