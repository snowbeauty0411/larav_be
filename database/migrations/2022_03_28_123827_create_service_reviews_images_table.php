<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceReviewsImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_reviews_images', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('reviews_id');
            $table->string('image_url');
            $table->timestamps();
            $table->foreign('reviews_id')->cascadeOnUpdate()->references('id')->on('service_reviews');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_reviews_images');
    }
}
