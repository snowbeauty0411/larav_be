<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->text('description');
            $table->integer('rating')->unsigned();
            $table->integer('buyer_id')->unsigned();
            $table->text('seller_reply')->nullable();
            $table->unsignedInteger('service_id');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_active_seller')->default(false);
            $table->unique(['buyer_id', 'service_id']);
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
        Schema::dropIfExists('service_reviews');
    }
}
