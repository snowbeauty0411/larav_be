<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_info', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('商号 ');
            $table->string('address')->comment('住所');
            $table->string('tel')->comment('電話番号');
            $table->string('fax')->comment('ファックス番号');
            $table->string('establish')->comment('設立');
            $table->string('capital')->comment('資本');
            $table->string('customer_banks')->comment('取引先銀行');
            $table->string('ceo')->comment('甘竹繁人');
            $table->string('director')->comment('取締役');
            $table->string('website_url')->comment('ウェブサイトURL ');
            $table->string('business_content')->comment('事業内容');
            $table->string('other')->comment('その他')->nullable();
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
        Schema::dropIfExists('company_info');
    }
}
