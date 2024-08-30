<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRainImageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rain_image', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('frammer_id')->unsigned()->nullable();//ไอดีเกษตรกร
            $table->integer('plotsugar_id')->unsigned()->nullable();//ไร่ที่เลือก
            $table->string('image', 255)->charset('utf8')->nullable();//รูปภาพ
            $table->string('year')->nullable();//วันที่เลือกกิจกรรม
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
        Schema::dropIfExists('rain_image');
    }
}
