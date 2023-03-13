<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePasswordResetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('password_resets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('account_id')->nullable()->comment('ユーザーID');
            $table->string('token')->comment('トークン');
            $table->timestamp('expiration')->nullable()->comment('有効期限');
            $table->timestamp('created_at')->nullable();
			$table->timestamp('updated_at')->nullable();

            $table->foreign('account_id')->cascadeOnUpdate()->references('id')->on('accounts');
        });

        DB::statement("ALTER TABLE password_resets COMMENT 'パスワード再設定'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('password_resets');
    }
}
