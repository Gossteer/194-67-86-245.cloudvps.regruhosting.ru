<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubscriptionCategoryIdToUsersReceivedRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_received_requests', function (Blueprint $table) {
            $table->unsignedInteger('request_id')->nullable();
            $table->foreign('request_id')->references('id')->on('requests')->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_received_requests', function (Blueprint $table) {
            $table->dropForeign('users_received_requests_request_id_foreign');
            $table->dropColumn('request_id');
            $table->dropForeign('users_received_requests_subscription_id_foreign');
            $table->dropColumn('subscription_id');
        });
    }
}
