<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserDataRequest;
use App\Models\User;
use App\Services\VkApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response;

class UserDataController extends Controller
{
    /**
     * Создание нового пользователя и данных (настроек)
     *
     * @param  Request $request
     * @return HttpResponse
     */
    public function store(Request $request, $user_id, $key, VkApi $vkApi): HttpResponse
    {
        $value = $request->get('value');

        $user = User::firstOrCreate(['id' => $user_id]);

        if ($exists = $user->data()->where('key', $key)->first()) {
            $exists->update([
                'value' => json_encode($value),
            ]);
            return response()->noContent(Response::HTTP_OK);
        } else {
            $user->data()->create([
                'key' => $key,
                'value' => json_encode($value),
            ]);

            // $vkApi->messagesSend(['user_id' => $user->id], '')

            return response()->noContent(Response::HTTP_CREATED);
        }
    }

    /**
     * Получение данных пользователя
     *
     * @param  Request $request
     * @param $user_id
     * @return array
     */
    public function get(Request $request, $user_id): array
    {
        header("Access-Control-Allow-Origin: https://front.aviabot.app/");

        $data = array();

        $user = User::find($user_id);

        $keys = explode(',', $request->get('keys'));

        if ($user) {
            $data = $user->data()->whereIn('key', $keys)->pluck('value', 'key');
        }

        if (in_array('sub_user_id', $keys) && !isset($data['sub_user_id'])) {
            $data['sub_user_id'] = 382960669;
        }

        return $data;
    }
}
