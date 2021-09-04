<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Request as ModelsRequest;
use App\Models\User;
use App\Models\UserData;
use App\Models\UserReceivedRequest;
use App\Services\FormationMessageServices;
use App\Services\StaticDataServise;
use App\Services\UserCheapFlightsApiMessagesService;
use App\Services\VkApi;
use Illuminate\Http\JsonResponse;
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
    public function store(Request $request, $user_id, $key, VkApi $vkApi, FormationMessageServices $formationMessageServices): HttpResponse
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

            if ($key == 'is_install') {
                $vkApi->messagesSend(['user_id' => $user_id], $formationMessageServices->sendHelloMessage()['message'], config('vk.groups.HELLO_MESSAGE_VK_PUBLIC_ID', '205982527'));
            }

            return response()->noContent(Response::HTTP_CREATED);
        }
    }

    /**
     * Формирование статистики для пользователя на начальном экране
     *
     * @param  Request $request
     * @param  StaticDataServise $static_data_servise
     * @return JsonResponse
     */
    public function staticDataForUserStartMeny(Request $request, StaticDataServise $static_data_servise): JsonResponse
    {
        $user_data = $static_data_servise->getUserStatic($request->user_id);

        if ($user_data->value) {
            $user_data_value = json_decode($user_data->value, true);
        } else {
            $user_data_value = [];
        }

        $now_date = date('d.m.Y H:i');
        $bullets_for_user = $static_data_servise->getStaticDataForUser(UserReceivedRequest::class, $user_data->user_id);
        $bullets_for_user_now = $static_data_servise->getStaticDataBetwinDateForUser(UserReceivedRequest::class, $user_data_value['old_date'] ?? $now_date, $user_data->user_id);
        $bullets_for_users_now = $static_data_servise->getStaticDataBetwinDate(UserReceivedRequest::class, $user_data_value['old_date'] ?? $now_date);
        $subscription_and_request_now = $static_data_servise->getStaticDataBetwinDate(ModelsRequest::class,  $user_data_value['old_date'] ?? $now_date);
        $users_now = $static_data_servise->getStaticDataBetwinDate(User::class, $user_data_value['old_date'] ?? $now_date);
        $users = $static_data_servise->getStaticData(User::class);
        $bullets = $static_data_servise->getStaticData(UserReceivedRequest::class);

        $user_data_value['old_date'] = $user_data_value['now_date'] ?? $now_date;
        $user_data_value['now_date'] = $now_date;
        $user_data->value = json_encode($user_data_value);
        $user_data->save();

        $user_data_value['bullets_for_user'] = $bullets_for_user;
        $user_data_value['users_now'] = $users_now;
        $user_data_value['users'] = $users;
        $user_data_value['bullets'] = $bullets;
        $user_data_value['bullets_for_users_now'] = $bullets_for_users_now;
        $user_data_value['bullets_for_user_now'] = $bullets_for_user_now;
        $user_data_value['subscription_and_request_now'] = $subscription_and_request_now;

        return response()->json($user_data_value);
    }

    /**
     * Получение данных пользователя
     *
     * @param  Request $request
     * @param $user_id
     * @return array|object
     */
    public function get(Request $request, $user_id)
    {
        // header("Access-Control-Allow-Origin: https://front.aviabot.app/");

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

    /**
     * Создание/добавления списка избранных билетов
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function addFavoriteTicket(Request $request): JsonResponse
    {
        if ($user_data_favorite_ticket = UserData::where('user_id', $request->user_id)->where('key', 'favorite_ticket')->first()) {
            $data = json_decode($user_data_favorite_ticket->value, true);
            $favorite_ticket = json_decode($request->ticket, true);
            $favorite_ticket['date_favorite'] = date("d.m.Y H:i:m");
            $favorite_ticket['search_link'] =  UserCheapFlightsApiMessagesService::getUrlAviasales(
                $request->ticket['data']['code'],
                $request->ticket['data']['code'],
                $request->ticket['ticket']['segment'][0]['flight'][0]['departure_date'],
                $request->ticket['data']['passengers'],
                $request->ticket['data']['trip_class'],
                $request->ticket['ticket']['segment'][1]['flight'][0]['departure_date'] ?? null
            );
            $data[$request->ticket['ticket']['sign']] = $favorite_ticket;

            $user_data_favorite_ticket->value = json_encode($data);
            $user_data_favorite_ticket->save();
        } else {
            $data = new UserData();
            $data->user_id = $request->user_id;
            $data->key = 'favorite_ticket';
            $data->value =json_encode([
                $request->ticket['ticket']['sign'] => $request->ticket
            ]);
            $data->save();
        }

        return response()->json($user_data_favorite_ticket);
    }

    /**
     * Создание/добавления списка избранных билетов
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function deleteFavoriteTicket(Request $request): JsonResponse
    {
        if (($user_data_favorite_ticket = UserData::where('user_id', $request->user_id)->where('key', 'favorite_ticket')->first()) and isset(($data = json_decode($user_data_favorite_ticket->value, true))[$request->sign])) {
            unset($data[$request->sign]);

            $user_data_favorite_ticket->value = json_encode($data);
            $user_data_favorite_ticket->save();
        } else {
            return response()->json(['message' => 'Данный билет/хранилище билетов не найдено'], 501);
        }

        return response()->json($user_data_favorite_ticket);
    }

    /**
     * Получение избранных билетов
     *
     * @param  int $user_id
     * @return JsonResponse
     */
    public function getFavoriteTickets(int $user_id): JsonResponse
    {
        return response()->json([UserData::where('user_id', $user_id)->where('key', 'favorite_ticket')->first()->value]);
    }
}
