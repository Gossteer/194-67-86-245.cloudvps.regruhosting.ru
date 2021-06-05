<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use App\Models\UserChat;
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
     * Проверка на доступ группы к отправке сообщений пользователю
     *
     * @param  $userId
     * @param  $groupId
     * @return JsonResponse
     */
    public function chat_allowed($userId, $groupId): JsonResponse
    {
        $isAllowed = false;
        $user = User::find($userId);
        if ($groupId) {
            if ($user) {
                $chat = $user->chats()->where([['chat_id', '=', $groupId]])->first();
                if ($chat) {
                    $data = file_get_contents("https://api.vk.com/method/messages.isMessagesFromGroupAllowed?access_token=" . $chat->token . "&group_id=" . $groupId . "&user_id=" . $userId . "&v=5.126");
                    $tmp = json_decode($data, true);
                    if (isset($tmp['response']['is_allowed'])) {
                        if ($tmp['response']['is_allowed'] == 1) {
                            $isAllowed = true;
                        }
                    }
                }
            }
        }

        return response()->json([
            'isAllowed' => $isAllowed
        ]);
    }

    /**
     * Зарегистрирован ли данный чат у пользователя в нашей системе
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function checkGroupEnable(Request $request): JsonResponse
    {
        $userId = $request->post('userId');
        $groupId = $request->post('groupId');
        $chat = UserChat::where(
            [
                ['user_id', '=', $userId],
                ['chat_id', '=', $groupId]
            ]
        )->first();

        return response()->json([
            'response' => $chat ? true : false
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
