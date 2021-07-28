<?php

namespace App\Http\Controllers;

use App\Models\Request as ModelsRequest;
use App\Models\Subscription;
use App\Models\UserReceivedRequest;
use App\Services\StaticDataServise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaticController extends Controller
{
    /**
     * Формирование статистики по всем данным для пользователя на начальном экране
     *
     * @param  StaticDataServise $static_data_servise
     * @return JsonResponse
     */
    public function getFullStaticForFirstInstall(StaticDataServise $static_data_servise): JsonResponse
    {
        return response()->json([
            'date_now' => date('d.m.Y'),
            'all_received_requests' => $static_data_servise->getStaticData(UserReceivedRequest::class),
            'all_requests' => $static_data_servise->getStaticData(ModelsRequest::class) + $static_data_servise->getStaticData(Subscription::class),
        ]);
    }
}
