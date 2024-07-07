<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFrammerAreaMixesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('frammer_area_mixes', function (Blueprint $table) {
            $table->increments('id');

            $table->string('code', 100)->unique()->charset('utf8');
            $table->string('year', 100)->unique()->charset('utf8');

            $table->integer('frammer_id')->unsigned()->index();
            $table->foreign('frammer_id')->references('id')->on('frammers')->onDelete('cascade');

            $table->integer('frammer_area_mix_event_type_id')->unsigned()->index();
            $table->foreign('frammer_area_mix_event_type_id')->references('id')->on('frammer_area_mix_event_types')->onDelete('cascade');

            $table->integer('frammer_area_mix_location_id')->unsigned()->index();
            $table->foreign('frammer_area_mix_location_id')->references('id')->on('frammer_area_mix_locations')->onDelete('cascade');

            $table->enum('status', ['Yes', 'No', 'Request'])->charset('utf8')->default('Request');
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
        Schema::dropIfExists('frammer_area_mixes');
    }
}
