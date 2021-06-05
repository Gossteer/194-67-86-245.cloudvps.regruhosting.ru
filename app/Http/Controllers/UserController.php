<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Создание нового пользователя и его подписки
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function saveRequest(Request $request): JsonResponse
    {
        $model = User::attachRequestToUser($request->post('user_id'), $request);

        return response()->json([
            'response' => 'ok',
            'id' => $model->id,
        ]);
    }
}
