<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFactoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('factories', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('factory_id')->unsigned()->nullable();//ไอดีเกษตรกร
            $table->string('code', 200)->unique()->charset('utf8');
            $table->string('name', 200)->unique()->charset('utf8');
            $table->text('address')->charset('utf8')->nullable();
            $table->string('phone', 100)->charset('utf8')->nullable();
            $table->string('email', 100)->charset('utf8')->nullable();

            $table->string('lat', 100)->charset('utf8')->nullable();
            $table->string('lon', 100)->charset('utf8')->nullable();

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
        Schema::dropIfExists('factories');
    }
}
