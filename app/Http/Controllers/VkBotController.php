<?php

namespace App\Http\Controllers;

use App\Services\VkApi2Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VkBotController extends Controller
{
    public function responseToMessage(Request $request)
    {
        Log::info("message lol", $request->all());

        switch ($request->type) {
            case 'confirmation':
                return 'e8819d89';
                break;
            case 'message_new':
                $vk_api_v2 = new VkApi2Services(null, '5.131');
                Log::info('response lol', json_decode($vk_api_v2->call(
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
                break;
            default:
                # code...
                break;
        }

    }
}
