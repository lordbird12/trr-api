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

            $table->string('code', 100)->unique()->charset('utf8');

            $table->integer('frammer_id')->unsigned()->index();
            $table->foreign('frammer_id')->references('id')->on('frammers')->onDelete('cascade');

            $table->integer('year')->nullable();
            $table->integer('contract_no')->nullable();
            $table->integer('test_no')->nullable();
            $table->integer('area')->nullable();
            $table->integer('all_area')->nullable();
            $table->integer('bonsucro')->nullable();
            $table->integer('gets_framing')->nullable();
            $table->integer('finish_good')->nullable();

            $table->string('country_code', 100)->charset('utf8');
            $table->string('province_code', 100)->charset('utf8');

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
        Schema::dropIfExists('frammer_areas');
    }
}
