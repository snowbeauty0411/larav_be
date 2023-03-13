<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_deliveries', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_id')->unique();
            $table->boolean('interval')->default(false);
            $table->integer('month_delivery')->unsigned()->nullable();
            $table->boolean('skip')->default(false);
            $table->integer('with_skip')->unsigned()->nullable();
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
        Schema::dropIfExists('service_deliveries');
    }
}
