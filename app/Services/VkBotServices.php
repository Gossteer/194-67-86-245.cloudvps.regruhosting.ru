<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VkBotServices
{
    private static array $func;
    private static array $func_message;

    private VkApi2Services $vk_api_v2;

    public function __construct()
    {
        $this->vk_api_v2 = new VkApi2Services(config('vk.api.VK_GROUP_API_TOKEN'), '5.131');

        self::$func_message = [
            'start' => function (int $peer_id, VkApi2Services $vk_api_v2): string {
                $this->log('response message_new', json_decode($this->messageSend($vk_api_v2->prepareMessageData(
                    'Добро пожаловать',
                    $peer_id,
                    $vk_api_v2->prepareKeyboard(false, false, [
                        'text' => [
                            [
                                'label' => 'healp',
                                'payload' => "{\"command\":\"healp\"}"
                            ],
                            [
                                'label' => 'Оформить подписку',
                                'payload' => "{\"command\":\"subscribe_now\"}"
                            ]
                        ]
                    ])
                )), true));

                return 'OK';
            },
            'error' => function (int $peer_id, VkApi2Services $vk_api_v2): string {
                $this->log('response message_new', json_decode($this->messageSend($vk_api_v2->prepareMessageData(
                    'Я вас не понял',
                    $peer_id,
                    $vk_api_v2->prepareKeyboard(false, false, [
                        'text' => [
                            [
                                'label' => 'healp',
                                'payload' => "{\"command\":\"healp\"}"
                            ],
                            [
                                'label' => 'Оформить подписку',
                                'payload' => "{\"command\":\"subscribe_now\"}"
                            ]
                        ]
                    ])
                )), true));

                return 'OK';
            },
            'subscribe_now' => function (int $peer_id, VkApi2Services $vk_api_v2): string {
                $this->log('response message_new', json_decode($this->messageSend($vk_api_v2->prepareMessageData(
                    'Сейчас оформим',
                    $peer_id,
                    $vk_api_v2->prepareKeyboard(false, false, [
                        'text' => [
                            [
                                'label' => 'healp',
                                'payload' => "{\"command\":\"healp\"}"
                            ],
                            [
                                'label' => 'Оформить подписку',
                                'payload' => "{\"command\":\"subscribe_now\"}"
                            ]
                        ]
                    ])
                )), true));

                return 'OK';
            }
        ];

        self::$func = [
            'confirmation' => static function () {
                return '06f42143';
            },
            'message_new' => function (Request $request, VkApi2Services $vk_api_v2): string {
                $fun_message = self::$func_message[$request->object['message']['text']] ??
                self::$func_message[json_decode($request->object['message']['payload'] ?? "{\"command\":\"error\"}", true)['command']];

                return $fun_message($request->object['message']['peer_id'], $vk_api_v2);
            },
        ];
    }

    public function main(Request $request): string
    {
        $this->log("message main", $request->all());

        return self::$func[$request->type]($request, $this->vk_api_v2);
    }

    private function log(string $message, ?array $context = []): void
    {
        Log::info($message, $context);
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
}
