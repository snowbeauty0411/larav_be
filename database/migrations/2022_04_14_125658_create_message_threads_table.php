<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessageThreadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_threads', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('seller_id')->nullable();
            $table->unsignedInteger('buyer_id')->nullable();
            $table->unsignedInteger('admin_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('seller_id')->cascadeOnUpdate()->references('id')->on('accounts');
            $table->foreign('buyer_id')->cascadeOnUpdate()->references('id')->on('accounts');
            $table->foreign('admin_id')->cascadeOnUpdate()->references('id')->on('admins');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('message_threads');
    }
}
