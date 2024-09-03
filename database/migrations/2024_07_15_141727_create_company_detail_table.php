<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_detail', function (Blueprint $table) {
            $table->increments('id');

            $table->string('head_office')->nullable();//สังกัดโรงงาน
            $table->string('phone', 100)->charset('utf8');//เบอร์
            $table->string('email', 100)->charset('utf8');//email
            $table->string('time_start', 100)->charset('utf8');//time
            $table->string('date_start', 100)->charset('utf8');//date
            $table->string('time_end', 100)->charset('utf8');//time
            $table->string('date_end', 100)->charset('utf8');//date
            $table->string('link1', 100)->charset('utf8');//youtube
            $table->string('link2', 100)->charset('utf8');//facebook
            $table->string('link3', 100)->charset('utf8');//tiktok
            $table->string('link4', 100)->charset('utf8');//website
            $table->string('link5', 100)->charset('utf8');//website
            $table->string('image1', 255)->charset('utf8')->nullable();//รูปภาพ
            $table->string('image2', 255)->charset('utf8')->nullable();//รูปภาพ
            $table->string('image3', 255)->charset('utf8')->nullable();//รูปภาพ
            $table->string('image4', 255)->charset('utf8')->nullable();//รูปภาพ
            $table->string('image5', 255)->charset('utf8')->nullable();//รูปภาพ
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_detail');
    }
}
