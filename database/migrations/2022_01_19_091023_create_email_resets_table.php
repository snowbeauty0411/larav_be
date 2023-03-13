<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailResetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_resets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->comment('ユーザーID');
            $table->string('new_email')->nullable()->comment('新メールアドレス');
            $table->string('auth_key')->comment('認証キー');
            $table->timestamp('expiration')->nullable()->comment('有効期限');
            $table->timestamp('created_at')->nullable();
            $table->foreign('user_id')->cascadeOnUpdate()->references('id')->on('users');
        });

        DB::statement("ALTER TABLE email_resets COMMENT 'メールアドレス再設定'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_resets');
    }
}
