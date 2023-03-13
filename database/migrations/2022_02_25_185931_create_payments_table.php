<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_store_buyer_id');
            $table->integer('sub_total');
            $table->integer('service_fee');
            $table->integer('total');
            $table->timestamp('pay_expire_at_date')->nullable();
            $table->string('stripe_charge_id')->nullable();
            $table->string('card_id');
            $table->unsignedInteger('payment_status')->default(0); //0:未払い  1:支払い確認済み
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
        Schema::dropIfExists('payments');
    }
}
