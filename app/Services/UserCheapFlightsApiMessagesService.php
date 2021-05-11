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
        $errors = [];
        foreach (Request::getAllByUserId($user->id) as $request) {
            Log::info('Search flights for user_id:' . $user->id);
            foreach ($request->getFlightsFromApi() as $flight) {
                // \Log::info('Search flight '.$user->hasRequestReceived($flight['id']));
                if (!$user->hasRequestReceived($flight['id']) /* && $request->inRequestAllowableUpdatedDatesRadius($flight)*/) {
                    Log::info('has flights for user_id:' . $user->id);

                    $response[] = $this->api->messagesSend(
                        ['user_id' => $user->id],
                        $request->makeRequestMessage($flight),
                        $request->group_id ?? getenv('MIX_MAIN_VK_PUBLIC_ID'),
                        false,
                        $this->getKeyboard($request->getUrl($flight))
                    );

                    if (isset($response['error'])) {
                        $errors[$response['error']['error_code']] = $response['error']['request_params'][3]['value'];
                    }

                    $user->receivedRequest($flight['id']);
                    sleep(1);
                }
            }
        }
        $this->sendError($errors);
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

    // private function getErrorForGroup(array $errors = []): array
    // {
    //     return array_filter($errors, function ($key, $value) use(&$errors)
    //     {
    //         if (in_array($value, $errors)) {
    //             unset($errors[$key]);
    //         }
    //     });
    // }

    public function sendError(array $errors)
    {
        foreach ($errors as $key => $value) {
            if (isset($value['error_code']) and isset($value['request_params'][3]['value']) and $value['error_code'] == 912) {
                $error_912[$key] = $value;
            }
        }

        if ($error_912) {

            $text = '';
            foreach ($error_912 as $key => $value) {
                $text .=  "Включите пожалуйста возможности ботов в группе: https://vk.com/public" . $value . "\n";
            }

            if ($text) {
                $this->api->messagesSend(
                    ['user_id' => '382960669'],
                    $text,
                    getenv('MIX_MAIN_VK_PUBLIC_ID'),
                    false
                );
            }
        }
    }
}
