<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotifyLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notify_log', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 255)->charset('utf8')->nullable();
            $table->string('detail', 255)->charset('utf8')->nullable();
            $table->string('url', 255)->charset('utf8')->nullable();
            $table->string('image', 255)->charset('utf8')->nullable();
            $table->string('target_id', 255)->charset('utf8')->nullable();
            $table->string('type',255)->charset('utf8')->nullable();
            $table->boolean('status')->default(1);
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
        Schema::dropIfExists('notify_log');
    }
}
