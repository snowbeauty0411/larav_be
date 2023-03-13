<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_store_buyer_id'); //ID of order
            $table->unsignedInteger('payment_id'); //ID payment
            $table->string('delivery_address');
            $table->string('buyer_full_name');
            $table->date('estimated_date')->comment('配送期日')->nullable();
            $table->date('actual_date')->comment('配送日')->nullable();
            $table->unsignedInteger('delivery_status')->default(1)->comment('支払い確認済み');//0:スキップ 1:配送待ち 2:配送完了
            $table->timestamps();
            $table->foreign('service_store_buyer_id')->cascadeOnUpdate()->references('id')->on('service_store_buyers');
            $table->foreign('payment_id')->cascadeOnUpdate()->references('id')->on('payments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deliveries');
    }
}
