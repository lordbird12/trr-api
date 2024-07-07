<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFrammerProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('frammer_products', function (Blueprint $table) {
            $table->increments('id');

            $table->string('year', 200)->charset('utf8');

            $table->integer('frammer_id')->unsigned()->index();
            $table->foreign('frammer_id')->references('id')->on('frammers')->onDelete('cascade');
            
            $table->integer('product')->nullable();
            $table->integer('promise')->nullable();
            $table->integer('size')->nullable();
            $table->integer('all_size')->nullable();
            $table->integer('bonsucro')->nullable();
            $table->integer('gets_framming')->nullable();
            $table->integer('product_per_size')->nullable();
            $table->double('ccs')->nullable();

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
        Schema::dropIfExists('frammer_products');
    }
}
