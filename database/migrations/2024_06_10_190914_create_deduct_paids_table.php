<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeductPaidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deduct_paids', function (Blueprint $table) {
            $table->increments('id');

            // $table->integer('frammer_id')->unsigned()->index();
            // $table->foreign('frammer_id')->references('id')->on('frammers')->onDelete('cascade');
            $table->integer('frammer_id')->unsigned()->nullable();//ไอดีเกษตรกร

            $table->integer('factory_activity_id')->unsigned()->index();
            $table->foreign('factory_activity_id')->references('id')->on('factory_activity')->onDelete('cascade');

            $table->integer('deduct_type_id')->unsigned()->index();
            $table->foreign('deduct_type_id')->references('id')->on('deduct_types')->onDelete('cascade');

            $table->string('code')->nullable();
            $table->double('paid', 10, 2)->default(0.00);

            $table->string('month', 2)->nullable();
            $table->string('year', 4)->nullable();

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
        Schema::dropIfExists('deduct_paids');
    }
}
