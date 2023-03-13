<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('message_thread_id')->comment('スレッドID');
            $table->unsignedInteger('from_id')->nullable()->comment('送信者ID');
            $table->unsignedInteger('admin_id')->nullable()->comment('管理者ID');
            $table->text('message_content')->nullable()->comment('内容');
            $table->string('file_name')->nullable()->comment('ファイル名');
            $table->string('file_path')->nullable()->comment('ファイルパス');
            $table->string('file_type')->nullable()->comment('ファイルタイプ');
            $table->timestamp('read_at')->nullable()->comment('既読日時');

            $table->foreign('message_thread_id')->cascadeOnUpdate()->references('id')->on('message_threads');
            $table->foreign('from_id')->cascadeOnUpdate()->references('id')->on('accounts');
            $table->foreign('admin_id')->cascadeOnUpdate()->references('id')->on('admins');
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
        Schema::dropIfExists('messages');
    }
}
