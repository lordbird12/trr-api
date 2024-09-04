<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetChatMsgsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_msg', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('chat_id')->nullable()->unsigned()->index();
            $table->foreign('chat_id')->references('id')->on('chat')->onDelete('cascade');

            //แอดมิน
            $table->integer('user_id')->nullable()->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('member_id')->nullable()->unsigned()->index();
            $table->foreign('member_id')->references('id')->on('member')->onDelete('cascade');

            $table->text('message')->charset('utf8')->nullable();
            $table->enum('type', ['text', 'image', 'file', 'url', 'emoji', 'close_deal', 'meeting'])->nullable()->charset('utf8')->default('text');

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
        Schema::dropIfExists('chat_msg');
    }
}
