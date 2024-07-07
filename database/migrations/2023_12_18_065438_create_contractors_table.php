<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contractors', function (Blueprint $table) {
            $table->increments('id');

            $table->string('code', 100)->unique()->charset('utf8');
            $table->string('name', 200)->unique()->charset('utf8');
            $table->string('phone', 100)->unique()->charset('utf8');
            $table->string('email', 100)->unique()->charset('utf8');

            $table->integer('contractor_type_id')->unsigned()->index();
            $table->foreign('contractor_type_id')->references('id')->on('contractor_types')->onDelete('cascade');

            $table->integer('feature_id')->unsigned()->index();
            $table->foreign('feature_id')->references('id')->on('features')->onDelete('cascade');

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
        Schema::dropIfExists('contractors');
    }
}
