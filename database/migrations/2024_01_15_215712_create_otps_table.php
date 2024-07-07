<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('otps', function (Blueprint $table) {
            $table->increments('id');

            $table->string('tel', 255)->charset('utf8')->nullable();
            $table->string('otp_ref', 255)->charset('utf8')->nullable();
            $table->string('otp_code', 255)->charset('utf8')->nullable();
            $table->string('otp_exp', 255)->charset('utf8')->nullable();
            $table->string('otp_type', 255)->charset('utf8')->nullable();
            $table->string('token', 255)->charset('utf8')->nullable();

            $table->boolean('status')->default(0);

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
        Schema::dropIfExists('otps');
    }
}
