<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFrammerAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('frammer_areas', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('frammer_id')->unsigned()->index();
            // $table->foreign('frammer_id')->references('id')->on('frammers')->onDelete('cascade');
            $table->integer('year')->nullable();
            $table->integer('area')->nullable();
            $table->double('area_size')->nullable();
            $table->integer('sugarcane_age')->nullable();
            $table->string('sugarcane_type', 200)->unique()->charset('utf8');
            $table->integer('product_per_rai')->nullable();
            $table->string('measuring_point', 200)->unique()->charset('utf8');
            $table->integer('distance')->nullable();
            $table->integer('last_year_cumulative_rain')->nullable();
            $table->integer('curr_year_cumulative_rain')->nullable();
            // $table->integer('all_area')->nullable();
            $table->string('create_by', 100)->charset('utf8')->nullable();
            $table->string('update_by', 100)->charset('utf8')->nullable();

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
        Schema::dropIfExists('frammer_areas');
    }
}
