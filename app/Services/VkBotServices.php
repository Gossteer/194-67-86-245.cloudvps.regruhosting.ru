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
    private static $request_vk;

    private UserData $user_data;

    const FUNK = [
        'search_tickets' => 'search_tickets',
        'start' => 'start',
        'subscribe_now' => 'subscribe_now',
        'healp' => 'healp',
        'error' => 'error',
        'back' => 'back'
    ];

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
            0 => function (): string {
                return $this->messageSend($this->vk_api_v2->prepareMessageData(
                    [
                        'message' => 'Необходимо выдать доступ данной группе',
                        'peer_id' => self::$request_vk->object['message']['peer_id'],
                        'attachment' => 'photo-206970444_457239017',
                    ],
                    $this->vk_api_v2->prepareKeyboard(false, false, [
                        [
                            'label' => 'Выдать доступ',
                            'link' => 'https://vk.com/public' . config('vk.groups.SEND_SUBSCRIPTION_SEARCH_VK_PUBLIC_ID', '205982619'),
                            'type' => 'open_link'
                        ],
                        [
                            [
                                'label' => 'Я выдал доступ',
                                'payload' => "{\"command\":\"" . self::FUNK['search_tickets'] . "\"}",
                                'type' => 'text'
                            ],
                            [
                                'label' => 'Главная',
                                'payload' => "{\"command\":\"" . self::FUNK['healp'] . "\"}",
                                'type' => 'text'
                            ]
                        ]
                    ])
                ));
            },
            1 => function (array $user_value): string {
                $user_value[self::FUNK['search_tickets']]['step'] = 2;
                $this->setUserValue($user_value);

                return $this->messageSend($this->vk_api_v2->prepareMessageData(
                    [
                        'message' => 'Введите пожалуйста город отправления',
                        'peer_id' => self::$request_vk->object['message']['peer_id'],
                    ],
                    $this->vk_api_v2->prepareKeyboard(false, false, [
                        [
                            'label' => 'Главная',
                            'payload' => "{\"command\":\"" . self::FUNK['healp'] . "\"}",
                            'type' => 'text'
                        ]
                    ])
                ));
            },
            2 => function (array $user_value): string {
                $user_value[self::FUNK['search_tickets']]['step'] = 2;
                $user_value[self::FUNK['search_tickets']]['data']['city'] = self::$request_vk->object['message']['message'];
                $this->setUserValue($user_value);

                return $this->messageSend($this->vk_api_v2->prepareMessageData(
                    [
                        'message' => 'Введите пожалуйста город назначения',
                        'peer_id' => self::$request_vk->object['message']['peer_id'],
                    ],
                    $this->vk_api_v2->prepareKeyboard(false, false, [
                        [
                            [
                                'label' => 'Главная',
                                'payload' => "{\"command\":\"" . self::FUNK['healp'] . "\"}",
                                'type' => 'text'
                            ],
                            [
                                'label' => 'Назад',
                                'payload' => "{\"command\":\"" . self::FUNK['back'] . "\"}",
                                'type' => 'text'
                            ]
                        ]
                    ])
                ));
            },
        ];
    }

    private function setFunc(): array
    {
        return [
            'confirmation' => static function () {
                exit('6c5abbe6');
            },
            'message_new' => function (): string {
                $func =
                    json_decode(self::$request_vk->object['message']['payload'] ?? '')['command']
                    ?? $this->getUserValue()['step']
                    ?? self::FUNK['error'];

                return self::$func_message[$func]();
            },
        ];
    }

    private function setFuncMessage(): array
    {
        return [
            self::FUNK['back'] => function (): string {
                $user_value = $this->getUserValue();
                $user_value[$user_value['step']]['step'] -= 1;
                $this->setUserValue($user_value);

                return self::$func_message[$user_value['step']]();
            },
            self::FUNK['start'] => function (): string {
                return $this->defaultMessageSend('Добро пожаловать', self::$request_vk->object['message']['peer_id']);
            },
            self::FUNK['error'] => function (): string {
                return $this->defaultMessageSend('Я вас не понял', self::$request_vk->object['message']['peer_id']);
            },
            self::FUNK['search_tickets'] => function (): string {
                $user_value = $this->getUserValue();

                if (!$this->chatAllowed($this->user_data->user_id, 205982619)) {
                    return self::$func_search_ticket[0]();
                }

                if (!isset($user_value[self::FUNK['search_tickets']])) {
                    $user_value[self::FUNK['search_tickets']] = [
                        'subscriptions' => [],
                        'step' => 1,
                        'data' => []
                    ];
                    $user_value['step'] = self::FUNK['search_tickets'];

                    $this->setUserValue($user_value);
                }

                return self::$func_search_ticket[$user_value[self::FUNK['search_tickets']]['step']]($user_value);
            },
            self::FUNK['subscribe_now'] => function (): string {
                return $this->defaultMessageSend('Сейчас оформим', self::$request_vk->object['message']['peer_id']);
            },
            self::FUNK['healp'] => function (): string {
                $user_value = $this->getUserValue();
                $user_value['step'] = null;
                $this->setUserValue($user_value);

                return $this->defaultMessageSend('Я вам не помощник', self::$request_vk->object['message']['peer_id']);
            }
        ];
    }

    private function getUserValue(): ?array
    {
        return json_decode($this->user_data->value, true);
    }

    private function setUserValue(array $user_value): bool
    {
        $this->user_data->value = json_encode($user_value);
        return $this->user_data->save();
    }

    public function main(Request $request): string
    {
        $this->log("request vk-bot", self::$request_vk = $request->all());

        try {
            $this->setUserData($request->object['message']['from_id'] ?? null);

            $this->log('response vk-bot', json_decode(self::$func[self::$request_vk['type']](), true) ?? []);
        } catch (\Throwable $th) {
            $this->log("error vk-bot :\n{$th->getMessage()}\n{$th->getLine()}", $th->getTrace());
        }

        return 'OK';
    }

    private function setUserData(?int $from_id)
    {
        if ($from_id && ($user_data = UserData::where('user_id', $from_id)->where('key', 'bot')->first())) {
            $this->user_data = $user_data;
        } elseif ($from_id) {
            $this->user_data = new UserData();
            $this->user_data->user_id = $from_id;
            $this->user_data->key = 'bot';
            $this->user_data->value = '';
            $this->user_data->save();
        }
    }

    private function log(string $message, ?array $context = []): void
    {
        Log::info($message, $context);
    }

    private function chatAllowed(int $user_id, int $group_id): bool
    {
        return json_decode($this->groupAllowed($this->vk_api_v2->prepareMessageData(
            [
                'group_id' => $group_id,
                'user_id' => $user_id,
                'access_token' => Chat::findOrFail($group_id)->token
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

    private function defaultMessageSend(string $text, int $peer_id): string
    {
        return $this->messageSend($this->vk_api_v2->prepareMessageData(
            [
                'message' => $text,
                'peer_id' => $peer_id
            ],
            $this->defaultKeyboard()
        ));
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
                    'payload' => "{\"command\":\"" . self::FUNK['subscribe_now'] . "\"}",
                    'type' => 'text'
                ],
                [
                    'label' => 'Поиск билетов',
                    'payload' => "{\"command\":\"" . self::FUNK['search_tickets'] . "\"}",
                    'type' => 'text'
                ]
            ],
            [
                'label' => 'Помощь',
                'payload' => "{\"command\":\"" . self::FUNK['healp'] . "\"}",
                'type' => 'text'
            ],
        ]);
    }
}
