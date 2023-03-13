<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_links', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url');
            $table->integer('jump_count');
            $table->timestamps();
            $table->foreign('id')->cascadeOnUpdate()->references('id')->on('services');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_links');
    }
}
