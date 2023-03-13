<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFavoritesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('buyer_id');
            $table->unsignedInteger('service_id');
            // $table->unsignedInteger('favorite_tag_id');
            $table->timestamps();
            $table->foreign('buyer_id')->cascadeOnUpdate()->references('account_id')->on('buyers');
            $table->foreign('service_id')->cascadeOnUpdate()->references('id')->on('services');
            // $table->foreign('favorite_tag_id')->cascadeOnUpdate()->references('id')->on('favorite_tags');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('favorites');
    }
}
