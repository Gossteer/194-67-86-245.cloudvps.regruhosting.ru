<?php

namespace App\Http\Controllers;

use App\Services\VkBotServices;
use Illuminate\Http\Request;

class VkBotController extends Controller
{
    public function responseToMessage(Request $request, VkBotServices $vk_bot_services)
    {
        return $vk_bot_services->main($request);
    }
}
