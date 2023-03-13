<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryServiceBuysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_service_buys', function (Blueprint $table) {
            $table->increments('history_service_buy_id');
            $table->unsignedInteger('buyer_id');
            $table->unsignedInteger('service_id');
            $table->timestamp('service_date_buy');
            $table->timestamp('service_date_cancel')->nullable();
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
        Schema::dropIfExists('history_service_buys');
    }
}
