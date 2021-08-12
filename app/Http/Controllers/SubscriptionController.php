<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    private SubscriptionService $subscription_service;

    public function __construct(SubscriptionService $subscription_service) {
        $this->subscription_service = $subscription_service;
    }

    public function createSupbscription(Request $request)
    {
        try {
            $supbscription = $this->subscription_service->createOrUpdateSupbscription($request);

            $response = 'ok';
            $code = 201;
        } catch (\Throwable $th) {
            $response = $th->getMessage();
            $code = 501;
        }


        return response()->json([
            'response' => $response,
            'supbscription' => $supbscription->load('subscriptionCategory')->only('id', 'data', 'last_date', 'period', 'subscription_category_id', 'subscriptionCategory')
        ], $code);
    }

    public function getSubscriptionsByUser(Request $request)
    {
        return response()->json(Subscription::select(
            'id',
            'data',
            'last_date',
            'period',
            'subscription_category_id',
            'updated_at',
            DB::raw('(select count(*) from users_received_requests where subscription_id = subscriptions.id) as count_send')
            )->with('subscriptionCategory')->where('user_id', $request->user_id)->where('subscription_category_id', $request->subscription_category_id)->get());
    }

    public function deleteSubscription($subscription_id)
    {
        Subscription::find($subscription_id)->delete();

        return response()->noContent();
    }
}
