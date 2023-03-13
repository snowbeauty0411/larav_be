<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceHoursTemps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_hours_temps', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_id');
            $table->date('date');
            $table->string('work_hour');
            $table->boolean('status')->default(false);
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
        Schema::dropIfExists('service_hours_temps');
    }
}
