<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoticesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notices', function (Blueprint $table) {
            $table->unsignedInteger('notice_permit_id');
            $table->unsignedInteger('account_id');
            $table->string('notice_name', 24)->default("");
            $table->boolean('notice_permit')->default(false);
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->primary(['notice_permit_id', 'account_id']);
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
        Schema::dropIfExists('notices');
    }
}
