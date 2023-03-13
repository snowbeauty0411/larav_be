<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuyerServiceReservesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buyer_service_reserves', function (Blueprint $table) {
            $table->unsignedInteger('buyer_id');
            $table->unsignedInteger('service_id');
            $table->char('course_id', 255);
            $table->timestamp('reserve_start')->nullable();
            $table->timestamp('reserve_end')->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->primary(['buyer_id', 'course_id', 'reserve_start']);
            $table->foreign('buyer_id')->cascadeOnUpdate()->references('account_id')->on('buyers');
            $table->foreign('service_id')->cascadeOnUpdate()->references('id')->on('services');
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
        Schema::dropIfExists('buyer_service_reserves');
    }
}
