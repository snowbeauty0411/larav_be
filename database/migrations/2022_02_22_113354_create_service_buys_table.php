<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceBuysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_buys', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('buyer_id');
            $table->unsignedInteger('service_id');
            $table->timestamp('buy_at')->nullable();
            $table->timestamp('cancel_at')->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->foreign('buyer_id')->cascadeOnUpdate()->references('account_id')->on('buyers');
            $table->foreign('service_id')->cascadeOnUpdate()->references('id')->on('services');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_buys');
    }
}
