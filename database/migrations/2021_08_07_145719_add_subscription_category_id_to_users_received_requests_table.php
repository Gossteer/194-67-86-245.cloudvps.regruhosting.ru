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
            $table->foreignId('subscription_category_id')->nullable()->constrained('subscription_categories');
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
            $table->dropColumn('subscription_category_id');
        });
    }
}
