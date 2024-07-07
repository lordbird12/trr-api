<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFrammerAreaMixLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('frammer_area_mix_locations', function (Blueprint $table) {
            $table->increments('id');

            $table->string('code', 100)->unique()->charset('utf8');

            $table->text('name')->nullable();
            $table->text('detail')->nullable();

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
        Schema::dropIfExists('frammer_area_mix_locations');
    }
}
