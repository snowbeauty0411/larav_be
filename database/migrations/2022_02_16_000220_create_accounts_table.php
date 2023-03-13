<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email', 128)->unique();
            $table->string('email_verify_token', 512)->nullable();
            $table->timestamp('email_verify_token_expiration', 0)->nullable();
            $table->timestamp('email_verified_at', 0)->nullable();
            $table->string('password');
            $table->char('classification_id', 2)->nullable(); //登録区分
            $table->char('phone_number', 12)->default(""); //電話番号
            $table->char('gender', 1)->nullable();
            $table->date('birth_day')->nullable();
            $table->char('postcode', 7)->default("");
            $table->char('business_type', 1)->nullable();
            $table->string('address_pref', 8)->default("");
            $table->string('address_city', 8)->default("");
            $table->string('address_other1', 8)->default("");
            $table->string('address_other2', 8)->default("");
            $table->datetime('admin_check_date')->nullable();
            $table->integer('identity_verification_status')->default(1);
            $table->boolean('idf_individual')->default(false);
            $table->boolean('idf_registery')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->timestamp('blocked_at')->nullable();
            $table->timestamp('last_login_at')->nullable(); //最終ログイン日時
            $table->timestamp('date_entry')->nullable(); //入会日時
            $table->timestamp('date_withdrawal')->nullable(); //退会日時
            $table->string('reason_withdrawal')->nullable();
            $table->unsignedInteger('message_mail_flg')->default(1); //0: No 1: Yes
            $table->unsignedInteger('transaction_mail_flg')->default(1);
            $table->unsignedInteger('favorite_service_mail_flg')->default(1);
            $table->unsignedInteger('recommend_service_mail_flg')->default(1);
            $table->unsignedInteger('system_maintenance_mail_flg')->default(1);
            $table->timestamp('deleted_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts');
    }
}
