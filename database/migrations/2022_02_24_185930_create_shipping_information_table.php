<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShippingInformationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_info', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('buyer_id');
            $table->string('last_name', 32)->nullable();
            $table->string('first_name', 32)->nullable();
            $table->string('last_name_kana', 32)->nullable();
            $table->string('first_name_kana', 32)->nullable();
            $table->char('phone', 12)->nullable();
            $table->char('post_code', 32)->nullable();
            $table->string('address')->nullable();
            $table->unsignedTinyInteger('is_default')->default(0); // 0: not default 1: default
            $table->timestamps();
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
        Schema::dropIfExists('shipping_info');
    }
}
