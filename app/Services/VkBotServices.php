<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VkBotServices
{
    private static $func;

    public function __construct()
    {
        self::$func = [
            'confirmation' => function () {
                return 'e05a6568';
            },
            'message_new' => function (Request $request): string {
                $vk_api_v2 = new VkApi2Services(null, '5.131');
                Log::info('response message_new', json_decode($vk_api_v2->call(
                    $vk_api_v2->prepareUrl(
                        'messages.send',
                        $vk_api_v2->prepareMessageData(
                            'Тест',
                            $request->object['message']['peer_id'],
                            $vk_api_v2->prepareKeyboard(false, false, [
                                'open_link' => [
                                    [
                                        'link' => "https://github.com/Gossteer/CheapFlights",
                                        'label' => 'Купить'
                                    ],
                                    [
                                        'link' => "https://habr.com/ru/company/ruvds/blog/438796/",
                                        'label' => 'Проверить цену'
                                    ]
                                ]
                            ])
                        )
                    )
                )->getBody()->getContents(), true));
                return 'OK';
            }
        ];
    }

    public function main(Request $request)
    {
        Log::info("message main", $request->all());

        return self::$func[$request->type]($request);
    }
}
