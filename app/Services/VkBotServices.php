<?php

namespace App\Services;

use App\Models\Chat;
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
                        'attachment' => 'photo-206970444_457239017',
                    ],
                    $this->vk_api_v2->prepareKeyboard(false, false, [
                        [
                            'label' => 'Выдать доступ',
                            'link' => 'https://vk.com/public' . config('vk.groups.SEND_SUBSCRIPTION_SEARCH_VK_PUBLIC_ID', '205982619'),
                            'type' => 'open_link'
                        ],
                        [
                            'label' => 'Главная',
                            'payload' => "{\"command\":\"healp\"}",
                            'type' => 'text'
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
                exit('6c5abbe6');
            },
            'message_new' => function (Request $request): void {
                $fun_message =
                    self::$func_message[json_decode($request->object['message']['payload'] ?? "{\"command\":\"error\"}", true)['command']]
                    ?? self::$func_message[$request->object['message']['text']]
                    ?? self::$func_message['error'];

                $fun_message($request->object['message']['peer_id']);
            },
        ];
    }

    private function setFuncMessage(): array
    {
        return [
            'start' => function (int $peer_id): void {
                $this->log('response message_new', json_decode($this->messageSend($this->vk_api_v2->prepareMessageData(
                    [
                        'message' => 'Добро пожаловать',
                        'peer_id' => $peer_id
                    ],
                    $this->defaultKeyboard()
                )), true));
            },
            'error' => function (int $peer_id): void {
                $this->log('response message_new', json_decode($this->messageSend($this->vk_api_v2->prepareMessageData(
                    [
                        'message' => 'Я вас не понял',
                        'peer_id' => $peer_id
                    ],
                    $this->defaultKeyboard()
                )), true));
            },
            'search_tickets' => function (int $peer_id): void {
                $user_value = json_decode($this->user_data->value, true);
                $respons = '';

                if ((!isset($user_value['search_tickets'])) && (!$this->chatAllowedSearchTicker($this->user_data->user_id))) {
                    $respons = self::$func_search_ticket[0]($peer_id);
                }

                $this->log('response message_new', json_decode($respons, true) ?? []);
            },
            'subscribe_now' => function (int $peer_id): void {
                $this->log('response message_new', json_decode($this->messageSend($this->vk_api_v2->prepareMessageData(
                    [
                        'message' => 'Сейчас оформим',
                        'peer_id' => $peer_id
                    ],
                    $this->defaultKeyboard()
                )), true));
            },
            'healp' => function (int $peer_id): void {
                $this->log('response message_new', json_decode($this->messageSend($this->vk_api_v2->prepareMessageData(
                    [
                        'message' => 'Я вам не помощник',
                        'peer_id' => $peer_id
                    ],
                    $this->defaultKeyboard()
                )), true));
            }
        ];
    }

    public function main(Request $request): string
    {
        $this->log("message main", $request->all());

        if (isset($request->object['message']['from_id'])) {
            if ($user_data = UserData::where('user_id', $request->object['message']['from_id'])->where('key', 'bot')->first()) {
                $this->user_data = $user_data;
            } else {
                $this->user_data = new UserData();
                $this->user_data->user_id = $request->object['message']['from_id'];
                $this->user_data->key = 'bot';
                $this->user_data->value = '';
                $this->user_data->save();
            }
        }

        self::$func[$request->type]($request);

        return 'OK';
    }

    private function log(string $message, ?array $context = []): void
    {
        Log::info($message, $context);
    }

    private function chatAllowedSearchTicker(int $user_id): bool
    {
        return json_decode($this->groupAllowed($this->vk_api_v2->prepareMessageData(
            [
                'group_id' => config('vk.groups.SEND_SUBSCRIPTION_SEARCH_VK_PUBLIC_ID', '205982619'),
                'user_id' => $user_id,
                'access_token' => Chat::find(205982619)->token
            ],
            $this->defaultKeyboard(),
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
                    'label' => 'Оформить подписку',
                    'payload' => "{\"command\":\"subscribe_now\"}",
                    'type' => 'text'
                ],
                [
                    'label' => 'Поиск билетов',
                    'payload' => "{\"command\":\"search_tickets\"}",
                    'type' => 'text'
                ]
            ],
            [
                'label' => 'Помощь',
                'payload' => "{\"command\":\"healp\"}",
                'type' => 'text'
            ],
        ]);
    }
}
