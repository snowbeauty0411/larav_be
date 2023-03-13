<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNumberAccessListServicePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('number_access_list_service_pages', function (Blueprint $table) {
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
        Schema::dropIfExists('number_access_list_service_pages');
    }
}
