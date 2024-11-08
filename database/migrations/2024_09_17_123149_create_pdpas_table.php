<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePdpasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pdpas', function (Blueprint $table) {
            $table->increments('id');
            
            $table->string('code', 255)->charset('utf8');

            $table->text('titie')->charset('utf8')->nullable();
            $table->text('detail')->charset('utf8')->nullable();

            $table->enum('status', ['Y', 'N'])->charset('utf8')->default('N');

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
        Schema::dropIfExists('pdpas');
    }
}
