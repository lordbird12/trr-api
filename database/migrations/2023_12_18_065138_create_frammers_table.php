<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFrammersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('frammers', function (Blueprint $table) {
            $table->increments('id');

            $table->string('qouta', 100)->unique()->charset('utf8');
            $table->string('idcard', 100)->unique()->charset('utf8');
            $table->string('name', 200)->unique()->charset('utf8');
            $table->string('phone', 100)->unique()->charset('utf8');
            $table->string('email', 100)->unique()->charset('utf8');

            $table->string('country_code', 100)->charset('utf8');
            $table->string('province_code', 100)->charset('utf8');

            $table->string('area', 100)->charset('utf8');
            $table->string('count_area', 100)->charset('utf8');

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
        Schema::dropIfExists('frammers');
    }
}
