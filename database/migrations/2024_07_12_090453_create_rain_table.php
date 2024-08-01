<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRainTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rain', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('frammer_id')->unsigned()->nullable();//ไอดีเกษตรกร
            // $table->foreign('frammer_id')->references('id')->on('frammers')->onDelete('cascade');
            $table->string('year')->nullable();//วันที่เลือกกิจกรรม

            $table->integer('plotsugar_id')->unsigned()->nullable();//ไร่ที่เลือก
            $table->double('last_year_cumulative_rain')->nullable();
            $table->double('curr_year_cumulative_rain')->nullable();
            $table->string('image', 255)->charset('utf8')->nullable();//รูปภาพ
            $table->json('co_or_points');
            $table->json('center');
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
        Schema::dropIfExists('rain');
    }
}
