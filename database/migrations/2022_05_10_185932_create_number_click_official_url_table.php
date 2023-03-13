<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNumberClickOfficialUrlTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('number_click_official_url', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('service_id');
            $table->unsignedInteger('count_by_month');
            $table->timestamps();
            $table->foreign('service_id')->cascadeOnUpdate()->cascadeOnDelete()->references('id')->on('services');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('number_click_official_url');
    }
}
