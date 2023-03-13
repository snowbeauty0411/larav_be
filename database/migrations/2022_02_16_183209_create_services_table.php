<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->unsignedInteger('id');
            $table->string('hash_id')->unique()->nullable();
            $table->unsignedInteger('seller_id');
            $table->string('name')->nullable();
            $table->unsignedInteger('service_type_id')->nullable();
            $table->string('caption')->nullable();
            $table->string('area')->nullable();
            $table->unsignedInteger('service_cat_id')->nullable();
            $table->string('service_content')->nullable();
            $table->string('address')->nullable();
            $table->string('zipcode')->nullable();
            $table->integer('max')->nullable();
            $table->boolean('private')->default(false);
            $table->boolean('enabled')->default(false);
            $table->boolean('is_draft')->default(true);
            $table->boolean('is_reserves')->default(false);
            // $table->char('age_confirm', 3)->nullable();
            $table->integer('sort')->nullable();
            $table->string('sort_type')->nullable();
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->timestamps();
            $table->primary(['id', 'seller_id']);
            $table->string('url_private')->unique()->nullable();
            $table->string('url_website')->nullable();
            $table->foreign('seller_id')->cascadeOnUpdate()->references('account_id')->on('sellers');
            $table->foreign('service_type_id')->cascadeOnUpdate()->references('id')->on('service_types');
            $table->foreign('service_cat_id')->cascadeOnUpdate()->references('id')->on('service_categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('services');
    }
}
