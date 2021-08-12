<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionCategory;
use App\Models\UserReceivedRequest;
use Illuminate\Http\Request;

class SubscriptionService
{
    public function createOrUpdateSupbscription(Request $request): Subscription
    {
        $data = $request->form;
        $data['ip'] = $request->ip;
        $data['low_price'] = $request->low_price ?? 1000;
        $period = $request->period ?? 1440;

        if ($request->date) {
            if (strtotime($request->date) > strtotime("now")) {
                $updated_at = date('Y-m-d H:i:s', strtotime($request->date));
            } else {
                $updated_at = date('Y-m-d H:i:s', strtotime($request->date . " + $period minutes"));
            }
        } else {
            $updated_at = date('Y-m-d H:i:s', strtotime("now + $period minutes"));
        }

        $data_subscription = [
            'date_end' => $request->date_end,
            'origin_code' => $data['src']['code'],
            'destination_code' => $data['dst']['code'],
            'origin_date' => $this->getDateFormatForMySql($data['date_src']),
            'destination_date' => $this->getDateFormatForMySql($data['date_dst'] ?? null),
            'data' => $data,
            'user_id' => $request->user_id,
            'subscription_category_id' => $request->subscription_category_id,
            'period' => $period,
            'updated_at' => $updated_at
        ];

        if ($request->last_data_response) {
            $data_subscription['last_data_response'] = $request->last_data_response;

            $current_price = $data_subscription['last_data_response']['terms'][array_key_first($data_subscription['last_data_response']['terms'])]['price'];
            $data_subscription['data']['statistic'][date('d.m.Y H:i:m')] = $current_price;
            $data_subscription['data']['сurrent_price'] = $current_price;
            $data_subscription['data']['first_price'] = $current_price;

            $subscription = Subscription::create($data_subscription);
            $subscription->updated_at = $updated_at;
            $subscription->save();
        } else {
            $subscription = Subscription::find($request->subscription_id);
            $subscription->update($data_subscription);
            $subscription->updated_at = $updated_at;
            $subscription->save();
        }

        return $subscription;
    }

    private function sendTPST(VkApi $vk_api, FormationMessageServices $formation_message_services, TravelPayoutsServices $travel_payouts_services)
    {
        $subscription_TPST = SubscriptionCategory::where('name', 'TPST')->with('subscriptions.user')->limit(1)->first();

        $date_now = date('Y-m-d H:i:s');

        $subscription_TPST->subscriptions()->where('origin_date', '<=',  $date_now)->where('subscription_category_id', $subscription_TPST->id)->delete();

        foreach ($subscription_TPST->subscriptions->where('origin_date', '>=',  $this->getDateFormatForMySql($date_now)) as $subscription) {
            $subscription_data = $subscription->data->toArray();

            if (date('Y-m-d H:i:s', strtotime($subscription_data['date_end'])) <= $date_now) {
                $subscription->delete();

                return 0;
            }

            if (strtotime(date('Y-m-d H:i:s', strtotime($subscription->updated_at))) <= strtotime($date_now)) {

                $low_after_now_search = $travel_payouts_services->searchResults($travel_payouts_services->searchTicketsArray($subscription->data->toArray()), 15, 15);

                if (!$subscription->user->hasRequestReceived($low_after_now_search[0]['search_id'])) {
                    $last_price = $subscription->last_data_response->toArray()['terms'][array_key_first($subscription->last_data_response->toArray()['terms'])]['price'];
                    $low_last_search = $last_price - $subscription_data['low_price'];
                    $max_last_search = $last_price + $subscription_data['low_price'];

                    $new_price = $low_after_now_search[0]['proposals'][0]['terms'][array_key_first($low_after_now_search[0]['proposals'][0]['terms'])]['price'];
                    if ($new_price < $low_last_search or $new_price > $max_last_search) {
                        $last_data_response = $low_after_now_search[0]['proposals'][0];
                        $last_data_response['old_price'] = $last_price;


                        $subscription->last_data_response = $last_data_response;
                        $subscription->last_date = $date_now;

                        $message = $formation_message_services->sendSubscriptionSearchTickets($subscription_data['dst'], $subscription_data['src'], $subscription->last_data_response->toArray(), $low_after_now_search[0]['airlines'], $low_after_now_search[0]['search_id'], $subscription_data['passengers'], $subscription_data['trip_class'], $subscription->created_at, $subscription_data['first_price']);

                        $vk_api->messagesSend(['user_id' => $subscription->user_id], $message['message'], config('vk.groups.SEND_SUBSCRIPTION_SEARCH_VK_PUBLIC_ID', '205982619'), true, $message['keyboard']);

                        $subscription->user->receivedRequest($low_after_now_search[0]['search_id'], $subscription->id);

                        $subscription_data['statistic'][date('d.m.Y H:i:m', strtotime($date_now))] = $new_price;
                        $subscription_data['сurrent_price'] = $new_price;
                        $subscription->data = $subscription_data;
                    }
                }

                $subscription->updated_at = date('Y-m-d H:i:s', strtotime($subscription->updated_at . "+ $subscription->period minutes"));
                $subscription->save();
            }
        }
    }

    public function sendSupbscription(VkApi $vk_api, FormationMessageServices $formation_message_services, TravelPayoutsServices $travel_payouts_services): int
    {
        $this->sendTPST($vk_api, $formation_message_services, $travel_payouts_services);

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
