<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionCategory;
use App\Models\UserReceivedRequest;
use Illuminate\Http\Request;

class SubscriptionService
{
    public function createSupbscription(Request $request)//: Subscription
    {
        $data = $request->form;
        $data['ip'] = $request->ip;

        $subscription = Subscription::create([
            'origin_code' => $data['src']['code'],
            'destination_code' => $data['dst']['code'],
            'origin_date' => $this->getDateFormatForMySql($data['date_src']),
            'destination_date' => $this->getDateFormatForMySql($data['date_dst'] ?? null),
            'data' => $data,
            'user_id' => $request->user_id,
            'last_data_response' => $request->last_data_response,
            'subscription_category_id' => $request->subscription_categorie_id,
        ]);

        return $subscription;
    }

    public function sendSupbscription(VkApi $vk_api, FormationMessageServices $formation_message_services, TravelPayoutsServices $travel_payouts_services)
    {
        $subscription_TPST = SubscriptionCategory::with('subscriptions.user')->limit(1)->first();

        $date_now = date('Y-m-d H:i:s');

        Subscription::where('origin_date', '<',  $this->getDateFormatForMySql($date_now))->where('subscription_category_id', $subscription_TPST->id)->delete();

        foreach ($subscription_TPST->subscriptions->where('origin_date', '>=',  $this->getDateFormatForMySql($date_now)) as $subscription) {
            $low_after_now_search = $travel_payouts_services->searchResults($travel_payouts_services->searchTicketsArray($subscription->data->toArray()), 15, 15);

            if (!$subscription->user->hasRequestReceived($low_after_now_search[0]['search_id'])) {
                $low_last_search = $subscription->last_data_response->toArray()['terms'][array_key_first($subscription->last_data_response->toArray()['terms'])]['price'];
                $subscription_data = $subscription->data->toArray();

                if ($low_after_now_search[0]['proposals'][0]['terms'][array_key_first($low_after_now_search[0]['proposals'][0]['terms'])]['price'] < $low_last_search) {
                    $last_data_response = $low_after_now_search[0]['proposals'][0];
                    $last_data_response['old_price'] = $low_last_search;

                    $subscription->last_data_response = $last_data_response;
                    $subscription->last_date = $date_now;

                    $message = $formation_message_services->sendSubscriptionSearchTickets($subscription_data['dst'], $subscription_data['src'], $subscription->last_data_response->toArray(), $low_after_now_search[0]['airlines'], $low_after_now_search[0]['search_id']);

                    $vk_api->messagesSend(['user_id' => $subscription->user_id], $message['message'], env('SEND_SUBSCRIPTION_SEARCH_VK_PUBLIC_ID', '205982527'), false, $message['keyboard']);

                    $subscription->save();

                    $subscription->user->receivedRequest($low_after_now_search[0]['search_id']);
                }
            }
        }

        return 0;
    }

    private function getDateFormatForMySql(?string $date): ?string
    {
        if ($date) {
            return date('Y-m-d H:i:s', strtotime($date));
        }

        return $date;
    }
}
