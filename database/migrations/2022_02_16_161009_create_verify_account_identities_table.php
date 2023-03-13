<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVerifyAccountIdentitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('verify_account_identities', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('account_id');
            $table->tinyInteger('type_id');
            $table->string('file1')->nullable();
            $table->string('file2')->nullable();
            $table->datetime('approval_at')->nullable();
            $table->datetime('denial_date')->nullable();
            $table->tinyInteger('identity_file_type')->nullable();
            $table->date('delete_date')->nullable();
            $table->timestamps();
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
        Schema::dropIfExists('verify_account_identities');
    }
}
