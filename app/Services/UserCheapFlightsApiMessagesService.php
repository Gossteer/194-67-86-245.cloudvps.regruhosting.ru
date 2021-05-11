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
                if (!$user->hasRequestReceived($flight['id']) /* && $request->inRequestAllowableUpdatedDatesRadius($flight)*/) {
                    Log::info('has flights for user_id:' . $user->id);
                    $test1 = $request->getUrl($flight);
                    $test2 = $this->getKeyboard($test1);
                    Log::info("getKeyboard wth url: $test1 $test1" . $user->id);
                    $this->api->messagesSend(
                        ['user_id' => $user->id],
                        $request->makeRequestMessage($flight),
                        $request->group_id ?? getenv('MIX_MAIN_VK_PUBLIC_ID'),
                        true,
                        $test2
                    );
                    $user->receivedRequest($flight['id']);
                    sleep(1);
                }
            }
        }
    }

    private function getKeyboard($url = null): ?array
    {
        if (isset($url)) {
            return [
                'one_time' => false,
                'inline' => true,
                "buttons" => [[
                    [
                        "action" => [
                            'type' => "open_link",
                            'link' => $url,
                            "label" => "Проверить цену"
                        ]
                    ],
                ]]
            ];
        }

        return null;
    }
}
