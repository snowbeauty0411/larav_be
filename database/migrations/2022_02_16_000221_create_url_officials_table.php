<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUrlOfficialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('url_officials', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url_official', 256)->nullable();
            $table->string('url_facebook', 256)->nullable();
            $table->string('url_instagram', 256)->nullable();
            $table->string('url_twitter', 256)->nullable();
            $table->string('url_sns_1', 256)->nullable();
            $table->string('url_sns_2', 256)->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('url_officials');
    }
}
