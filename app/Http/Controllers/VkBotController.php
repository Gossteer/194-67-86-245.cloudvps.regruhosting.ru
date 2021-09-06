<?php

namespace App\Http\Controllers;

use App\Services\VkApi2Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VkBotController extends Controller
{
    public function responseToMessage(Request $request)
    {
        Log::info("message", $request);

        switch ($request->type) {
            case 'confirmation':
                return 'aa952678';
                break;
            case 'message_new':
                $vk_api_v2 = new VkApi2Services(null, '5.87');
                $vk_api_v2->call(
                    $vk_api_v2->prepareUrl(
                        'messages.send',
                        $vk_api_v2->prepareMessageData('Тест', $request->object->peer_id)
                    )
                );
                break;
            default:
                # code...
                break;
        }
    }
}
