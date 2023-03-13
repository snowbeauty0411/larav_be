<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceReservesSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_reserves_setting', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_id')->unique();
            $table->boolean('is_enable')->default(0);
            $table->integer('max')->nullable();
            $table->string('time_distance')->nullable();
            $table->integer('duration_before')->nullable();
            $table->integer('duration_after')->nullable();
            $table->integer('type_duration_after')->default(1);
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
        Schema::dropIfExists('service_reserves_setting');
    }
}
