<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuyersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buyers', function (Blueprint $table) {
            $table->increments('account_id');
            $table->string('account_name', 32)->default(""); //アカウント名
            $table->string('first_name', 8)->default(""); //氏名（名
            $table->string('last_name', 8)->default(""); //氏名（氏）
            $table->char('gender', 1)->nullable();
            $table->unsignedInteger('url_official_id')->nullable(); //公式URLID
            $table->string('profile_text_buy', 1000)->default(""); //プロフィール文
            $table->string('profile_image_url_buy', 512)->nullable(); //プロフィール画像URL
            $table->char('stripe_customer_id', 18)->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->foreign('account_id')->cascadeOnUpdate()->references('id')->on('accounts');
            $table->foreign('url_official_id')->cascadeOnUpdate()->references('id')->on('url_officials');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('buyers');
    }
}
