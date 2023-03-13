<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceBrowsingHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_browsing_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ip_address');
            $table->unsignedInteger('service_id');
            $table->timestamps();
            $table->foreign("service_id")->cascadeOnUpdate()->references('id')->on('services');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_browsing_histories');
    }
}
