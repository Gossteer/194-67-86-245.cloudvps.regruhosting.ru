<?php

namespace App\Http\Controllers;

use App\Services\FormationMessageServices;
use App\Services\SubscriptionService;
use App\Services\TravelPayoutsServices;
use App\Services\VkApi;
use Illuminate\Http\Request;
use App\Models\User;

class SubscriptionController extends Controller
{
    private SubscriptionService $subscription_service;

    public function __construct(SubscriptionService $subscription_service) {
        $this->subscription_service = $subscription_service;
    }

    public function createSupbscription(Request $request)
    {
        try {
            $supbscription = $this->subscription_service->createSupbscription($request);

            $response = 'ok';
            $code = 201;
        } catch (\Throwable $th) {
            $response = $th->getMessage();
            $code = 501;
        }


        return response()->json([
            'response' => $response
        ], $code);
    }

    public function sendSupbscription(VkApi $vk_api, FormationMessageServices $formation_message_services, TravelPayoutsServices $travel_payouts_services)
    {
        try {
            $supbscription = $this->subscription_service->sendSupbscription($vk_api, $formation_message_services, $travel_payouts_services);

            $response = 'ok';
            $code = 201;
        } catch (\Throwable $th) {
            $response = $th->getMessage();
            $code = 501;
        }


        return response()->json([
            'response' => $response
        ], $code);
    }
}
