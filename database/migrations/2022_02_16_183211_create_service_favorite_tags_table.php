<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceFavoriteTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_favorite_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_id')->unsigned();
            $table->integer('favorite_tag_id')->unsigned();
            $table->timestamps();
            $table->foreign('service_id')->cascadeOnUpdate()->references('id')->on('services');
            $table->foreign('favorite_tag_id')->cascadeOnUpdate()->references('id')->on('favorite_tags');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_favorite_tags');
    }
}
