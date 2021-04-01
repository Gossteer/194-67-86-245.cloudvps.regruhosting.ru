<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            Schema::create('user_chats', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->integer('chat_id')->unsigned();
                $table->timestamps();
            });
            Schema::create('chats', function (Blueprint $table) {
                $table->increments('id');
                $table->string('token');
            });
            Schema::table('user_chats', function (Blueprint $table) {
                $table->foreign('chat_id')->references('id')->on('chats');
            });
            \Illuminate\Support\Facades\DB::table('chats')->insert([
                ['id' => 166870081, 'token' => '4c32286fede35ae78bd47743fa430b8d978731b0ec424270aacb987c3e44955f6464ae824ace65f3b30a3'],
                ['id' => 188785799, 'token' => '7437f81bd611fae6a1dcb8b87f1601748de208140611044a5780b35513cf3c2a514e870f339473bb9807c'],
            ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::dropIfExists('user_chats');
    }
}
