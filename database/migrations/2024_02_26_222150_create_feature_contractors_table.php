<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeatureContractorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feature_contractors', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('contractor_id')->unsigned()->index();
            $table->foreign('contractor_id')->references('id')->on('contractors')->onDelete('cascade');

            $table->integer('feature_id')->unsigned()->index();
            $table->foreign('feature_id')->references('id')->on('features')->onDelete('cascade');

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
        Schema::dropIfExists('feature_contractors');
    }
}
