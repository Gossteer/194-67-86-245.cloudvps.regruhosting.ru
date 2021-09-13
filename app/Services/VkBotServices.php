<?php

namespace App\Services;

use App\Models\UserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VkBotServices
{
    private static array $func;
    private static array $func_message;
    private static array $func_search_ticket;
    private UserData $user_data;

    private VkApi2Services $vk_api_v2;

    public function __construct()
    {
        $this->vk_api_v2 = new VkApi2Services(config('vk.api.VK_GROUP_API_TOKEN'), '5.131');

        self::$func_search_ticket = $this->funcSearchTicket();
        self::$func_message = $this->setFuncMessage();
        self::$func = $this->setFunc();
    }

    private function funcSearchTicket(): array
    {
        return [
            0 => function (int $peer_id): string {
                return $this->messageSend($this->vk_api_v2->prepareMessageData(
                    [
                        'message' => 'Необходимо выдать доступ данной группе',
                        'peer_id' => $peer_id,
                        'attachment' => 'https://vk.com/photo-206970444_457239017',
                    ],
                    $this->vk_api_v2->prepareKeyboard(false, false, [
                        'open_link' => [
                            [
                                'label' => 'Выдать доступ',
                                'link' => 'https://vk.com/public' . config('vk.groups.SEND_SUBSCRIPTION_SEARCH_VK_PUBLIC_ID', '205982619')
                            ]
                        ]
                    ])
                ));
            }
        ];
    }

    private function setFunc(): array
    {
        return [
            'confirmation' => static function () {
                return '06f42143';
            },
            'message_new' => function (Request $request): string {
                $fun_message =
                    self::$func_message[json_decode($request->object['message']['payload'] ?? "{\"command\":\"error\"}", true)['command']]
                    ?? self::$func_message[$request->object['message']['text']]
                    ?? self::$func_message['error'];

                return $fun_message($request->object['message']['peer_id']);
            },
        ];
    }

    private function setFuncMessage(): array
    {
        return [
            'start' => function (int $peer_id): string {
                $this->log('response message_new', json_decode($this->messageSend($this->vk_api_v2->prepareMessageData(
                    [
                        'message' => 'Добро пожаловать',
                        'peer_id' => $peer_id
                    ],
                    $this->defaultKeyboard()
                )), true));

                return 'OK';
            },
            'error' => function (int $peer_id): string {
                $this->log('response message_new', json_decode($this->messageSend($this->vk_api_v2->prepareMessageData(
                    [
                        'message' => 'Я вас не понял',
                        'peer_id' => $peer_id
                    ],
                    $this->defaultKeyboard()
                )), true));

                return 'OK';
            },
            'search_tickets' => function (int $peer_id): string {
                $user_value = json_decode($this->user_data->value, true);
                $respons = '';

                if ((!isset($user_value['search_tickets'])) && (!$this->chatAllowedSearchTicker($this->user_data->user_id))) {
                    $respons = self::$func_search_ticket[0]($peer_id);
                }

                $this->log('response message_new', json_decode($respons, true));

                return 'OK';
            },
            'subscribe_now' => function (int $peer_id): string {
                $this->log('response message_new', json_decode($this->messageSend($this->vk_api_v2->prepareMessageData(
                    [
                        'message' => 'Сейчас оформим',
                        'peer_id' => $peer_id
                    ],
                    $this->defaultKeyboard()
                )), true));

                return 'OK';
            },
            'healp' => function (int $peer_id): string {
                $this->log('response message_new', json_decode($this->messageSend($this->vk_api_v2->prepareMessageData(
                    [
                        'message' => 'Я вам не помощник',
                        'peer_id' => $peer_id
                    ],
                    $this->defaultKeyboard()
                )), true));

                return 'OK';
            }
        ];
    }

    public function main(Request $request): string
    {
        $this->log("message main", $request->all());

        if ($user_data = UserData::where('user_id', $request->object['message']['from_id'])->where('key', 'bot')->first()) {
            $this->user_data = $user_data;
        } else {
            $this->user_data = new UserData();
            $this->user_data->user_id = $request->object['message']['from_id'];
            $this->user_data->key = 'bot';
            $this->user_data->value = '';
            $this->user_data->save();
        }

        return self::$func[$request->type]($request);
    }

    private function log(string $message, ?array $context = []): void
    {
        Log::info($message, $context);
    }

    public function chatAllowedSearchTicker(int $user_id)
    {
        return json_decode($this->groupAllowed($this->vk_api_v2->prepareMessageData(
            [
                'group_id' => config('vk.groups.SEND_SUBSCRIPTION_SEARCH_VK_PUBLIC_ID', '205982619'),
                'user_id' => $user_id
            ],
            $this->defaultKeyboard()
        )), true)['response']['is_allowed'];
    }

    private function groupAllowed(array $params): string
    {
        return $this->vk_api_v2->call(
            $this->vk_api_v2->prepareUrl(
                'messages.isMessagesFromGroupAllowed',
                $params
            )
        )->getBody()->getContents();
    }

    private function messageSend(array $params): string
    {
        return $this->vk_api_v2->call(
            $this->vk_api_v2->prepareUrl(
                'messages.send',
                $params
            )
        )->getBody()->getContents();
    }

    private function defaultKeyboard(): string
    {
        return $this->vk_api_v2->prepareKeyboard(false, false, [
            [
                [
                    'label' => 'Помощь',
                    'payload' => "{\"command\":\"healp\"}",
                    'type' => 'text'
                ],
                [
                    'label' => 'Оформить подписку',
                    'payload' => "{\"command\":\"subscribe_now\"}",
                    'type' => 'text'
                ],
                [
                    'label' => 'Поиск билетов',
                    'payload' => "{\"command\":\"search_tickets\"}",
                    'type' => 'text'
                ]
            ]
        ]);
    }
}
