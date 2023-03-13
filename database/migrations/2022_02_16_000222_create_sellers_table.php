<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->increments('account_id');
            $table->string('account_name', 32)->default(""); //アカウント名
            $table->string('first_name', 8)->default(""); //氏名（名
            $table->string('last_name', 8)->default(""); //氏名（氏）
            $table->char('gender', 1)->nullable();
            $table->unsignedInteger('business_id')->nullable(); //業種ID
            $table->unsignedInteger('url_official_id')->nullable(); //公式URLID
            $table->string('profile_text_sell', 1000)->default(""); //プロフィール文
            $table->string('profile_image_url_sell', 512)->nullable(); //プロフィール画像URL
            $table->char('stripe_connect_id', 25)->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->foreign('url_official_id')->cascadeOnUpdate()->references('id')->on('url_officials');
            $table->foreign('account_id')->cascadeOnUpdate()->references('id')->on('accounts');
            $table->foreign('business_id')->cascadeOnUpdate()->references('business_id')->on('businesses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sellers');
    }
}
