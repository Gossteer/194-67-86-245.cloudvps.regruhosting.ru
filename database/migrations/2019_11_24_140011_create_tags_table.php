<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagsTable extends Migration
{
    public function up()
    {
        Schema::create('tags', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->increments('id');
            $table->string('body');
            $table->timestamps();
        });
        Schema::create('users_tags', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('tag_id')->unsigned()->nullable();
            $table->timestamps();
        });
        Schema::table('users_tags', function (Blueprint $table) {
            $table->foreign('tag_id')->references('id')->on('tags');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users_tags');
        Schema::drop('tags');
    }
}
