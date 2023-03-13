<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSendOtpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('send_otp', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('account_id')->nullable();
            $table->string('email')->nullable();
            $table->unsignedInteger('otp_type');
            $table->unsignedInteger('otp');
            $table->timestamp('otp_expire_at');
            $table->timestamps();
            $table->foreign('account_id')->cascadeOnUpdate()->references('id')->on('accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('send_otp');
    }
}
