<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTermOfServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('term_of_services', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->comment('タイトル');
            $table->text('text')->comment('内容');
            $table->integer('sort')->default(0)->comment('表示順');
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
        Schema::dropIfExists('term_of_services');
    }
}
