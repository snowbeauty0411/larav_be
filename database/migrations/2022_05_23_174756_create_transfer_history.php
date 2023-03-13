<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfer_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('seller_id');
            $table->integer('transfer_amount');
            $table->integer('transfer_fee');
            $table->boolean('status')->default(false);
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->foreign('seller_id')->cascadeOnUpdate()->references('account_id')->on('sellers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transfer_history');
    }
}
