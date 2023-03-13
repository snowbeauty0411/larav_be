<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoginHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('login_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->ipAddress('ip_address')->comment('IPアドレス');
            $table->tinyInteger('count_failed')->default(0)->comment('ログイン失敗をカウント');
            $table->timestamp('block_at')->nullable()->comment('ブロッキング時間');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("ALTER TABLE login_histories COMMENT 'ログイン履歴'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('login_histories');
    }
}
