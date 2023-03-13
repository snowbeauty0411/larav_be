<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellerCardInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seller_card_info', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('bank_name')->nullable();
            $table->unsignedInteger('bank_id')->nullable();
            $table->string('branch_code')->nullable();
            $table->string('account_number')->nullable();
            $table->tinyInteger('account_type')->nullable();
            $table->string('first_name_account')->nullable();
            $table->string('last_name_account')->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->foreign('id')->cascadeOnUpdate()->references('account_id')->on('sellers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seller_card_info');
    }
}
