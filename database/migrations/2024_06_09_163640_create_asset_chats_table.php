<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat', function (Blueprint $table) {
            $table->increments('id');

            $table->string('room_name', 255)->charset('utf8');
            //สมาชิก
            $table->integer('member_id')->nullable()->unsigned()->index();
            $table->foreign('member_id')->references('id')->on('member')->onDelete('cascade');

            $table->enum('type', ['deal', 'vip'])->nullable()->charset('utf8')->default('deal');

            $table->integer('close_deal')->default(0);
            $table->integer('meeting')->default(0);
            $table->integer('co_agent')->default(0);

            $table->enum('status', ['chat', 'finish', 'close'])->nullable()->charset('utf8')->default('chat');

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
        Schema::dropIfExists('chat');
    }
}
