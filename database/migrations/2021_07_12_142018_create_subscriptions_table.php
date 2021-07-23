<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('origin_code')->comment('Код страны отправления');
            $table->string('destination_code')->comment('Код страны назначения');
            $table->dateTime('origin_date')->comment('Дата отправления');
            $table->dateTime('destination_date')->nullable()->comment('Дата обратного вылета');
            $table->json('data')->nullable()->comment('Доп данные для подписки');
            $table->json('last_data_response')->nullable()->comment('Последние данные по подписке');
            $table->dateTime('last_date')->nullable()->comment('Последния дата обновление последних полученных данных');
            $table->unsignedInteger('user_id');
            $table->integer('period')->nullable()->comment('Частота отправки подписки в минутах, смотрится в первую очередь, потом в категории');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreignId('subscription_category_id')->constrained('subscription_categories');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}
