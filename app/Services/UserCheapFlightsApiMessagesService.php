<?php

namespace App\Services;

use App\Models\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserCheapFlightsApiMessagesService
{
    private $api;

    public function __construct()
    {
        $this->api = new VkApi();
    }

    public function sendApiMessagesToAllUsers()
    {
        $users = User::all();
        foreach ($users as $user) {
            $this->sendUserAllMessages($user);
        }
    }

    public function sendUserAllMessages($user)
    {
        foreach (Request::getAllByUserId($user->id) as $request) {
            Log::info('Search flights for user_id:' . $user->id);
            foreach ($request->getFlightsFromApi() as $flight) {
                // \Log::info('Search flight '.$user->hasRequestReceived($flight['id']));
                $makeRequestMessage = $request->makeRequestMessage($flight);
                Log::info($makeRequestMessage['fullUrl']);
                if (!$user->hasRequestReceived($flight['id']) /* && $request->inRequestAllowableUpdatedDatesRadius($flight)*/) {
                    Log::info('has flights for user_id:' . $user->id);
                    $makeRequestMessage = $request->makeRequestMessage($flight);
                    $this->api->messagesSend(
                        ['user_id' => $user->id],
                        $makeRequestMessage['message'],
                        $request->group_id ?? getenv('MIX_MAIN_VK_PUBLIC_ID'),
                        $makeRequestMessage['fullUrl'],
                    );
                    $user->receivedRequest($flight['id']);
                    sleep(1);
                }
            }
        }
    }
}
