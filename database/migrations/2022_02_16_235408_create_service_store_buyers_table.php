<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceStoreBuyersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void 
     */
    public function up()
    {
        Schema::create('service_store_buyers', function (Blueprint $table) {
            $table->increments('id');
            $table->char('course_id', 255)->unset();
            $table->unsignedInteger('buyer_id');
            $table->char('qrUrl', 255)->nullable();
            $table->integer('count')->nullable();
            $table->datetime('start')->nullable();
            $table->datetime('end')->nullable();
            $table->integer('status')->default(0);
            $table->timestamp('buy_at')->nullable();
            $table->timestamp('cancel_at')->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->foreign('course_id')->cascadeOnUpdate()->references('course_id')->on('service_courses');
            $table->foreign('buyer_id')->cascadeOnUpdate()->references('account_id')->on('buyers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_store_buyers');
    }
}
