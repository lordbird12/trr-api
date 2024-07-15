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

            $table->integer('factory_id')->unsigned()->nullable();//ไอดีบริษัท

            $table->string('factory_affiliation')->nullable();//สังกัดโรงงาน
            $table->string('head_office')->nullable();//สังกัดโรงงาน
            $table->string('phone', 100)->charset('utf8');//เบอร์
            $table->string('email', 100)->charset('utf8');//email
            $table->string('time_start', 100)->charset('utf8');//time
            $table->string('date_start', 100)->charset('utf8');//date
            $table->string('time_end', 100)->charset('utf8');//time
            $table->string('date_end', 100)->charset('utf8');//date
            $table->string('youtube', 100)->charset('utf8');//youtube
            $table->string('facebook', 100)->charset('utf8');//facebook
            $table->string('tiktok', 100)->charset('utf8');//tiktok
            $table->string('website', 100)->charset('utf8');//website
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
