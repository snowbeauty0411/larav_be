<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeForeignKeyEmailResetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_resets', function (Blueprint $table) {
            $table->dropForeign('email_resets_user_id_foreign');
            $table->foreign('user_id')->cascadeOnUpdate()->references('id')->on('accounts');
          });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
