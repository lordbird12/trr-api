<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeductTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deduct_types', function (Blueprint $table) {
            $table->increments('id');

            $table->string('code');
            $table->string('name');
            $table->string('short_name')->nullable();

            $table->boolean('status')->default(1);
            $table->string('create_by', 100)->nullable();
            $table->string('update_by', 100)->nullable();
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
        Schema::dropIfExists('deduct_types');
    }
}
