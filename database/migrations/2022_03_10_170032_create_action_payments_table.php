<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActionPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('action_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_store_buyer_id')->unique();
            $table->string('buyer_full_name')->nullable();
            $table->string('delivery_address')->nullable();
            $table->string('card_id');
            $table->boolean('skip')->default(false);
            $table->datetime('charge_at');
            $table->timestamps();
            $table->foreign('service_store_buyer_id')->cascadeOnUpdate()->references('id')->on('service_store_buyers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('action_payments');
    }
}
