<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecommendHashtagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recommend_hashtags', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tag_id');
            $table->unsignedBigInteger('count');
            $table->timestamps();
            $table->foreign('tag_id')->cascadeOnUpdate()->references('id')->on('tags');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recommend_hashtags');
    }
}
