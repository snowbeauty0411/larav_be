<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceDeliveryBuyersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_delivery_buyers', function (Blueprint $table) {
            $table->increments('id');
            $table->char('course_id', 255)->unique();
            $table->unsignedInteger('buyer_id');
            $table->datetime('start');
            $table->datetime('end');
            $table->char('address', 255);
            $table->timestamps();
            $table->foreign('course_id')->cascadeOnUpdate()->references('course_id')->on('service_courses');
            $table->foreign('buyer_id')->cascadeOnUpdate()->references('account_id')->on('buyers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_delivery_buyers');
    }
}
