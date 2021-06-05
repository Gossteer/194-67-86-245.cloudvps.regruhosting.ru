<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Создание чата, берёт из $request->id группы и её $request->token
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        Chat::createNewOne($request->id, $request->token);

        return response()->json([
            'response' => 'ok'
        ]);
    }

    /**
     * Возвращает список чатов
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function list()
    {
        return Chat::pluck('id');
    }
}
