<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecommendServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recommend_services', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_id');
            $table->unsignedInteger('buyer_id');
            $table->unsignedBigInteger('count');
            $table->timestamps();
            $table->foreign('service_id')->cascadeOnUpdate()->references('id')->on('services');
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
        Schema::dropIfExists('recommend_services');
    }
}
