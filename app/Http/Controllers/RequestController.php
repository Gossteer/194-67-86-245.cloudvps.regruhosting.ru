<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use App\Models\Group;
use App\Services\FormationMessageServices;
use App\Services\TravelPayoutsServices;
use App\Services\VkApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

use function GuzzleHttp\json_decode;

class RequestController extends Controller
{
    /**
     * Получение всех подписок пользователя по $request->user_id
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function all(Request $request): JsonResponse
    {
        $userId = $request->post('user_id');
        $requests = RequestModel::where(['user_id' => $userId])->orderBy('id', 'DESC')->get();
        for ($i = 0; $i < count($requests); $i++) {
            $group_id = $requests[$i]->group_id;
            $data = file_get_contents("https://api.vk.com/method/groups.getById?access_token=6070d8a06070d8a06070d8a03660036322660706070d8a03f3e1814c6491f7856970679&group_id=" . $group_id . "&v=5.124");
            $tmp = json_decode($data, true);
            $requests[$i]->group_name = isset($group_id) ? $tmp['response'][0]['name'] : "";
            $requests[$i]->group_photo = isset($group_id) ? $tmp['response'][0]['photo_200'] : "";
            $requests[$i]->group_type = Group::getGroupType($group_id);
            $requests[$i]['output'] = json_decode($requests[$i]['output'], true);
            $requests[$i]['content'] = json_decode($requests[$i]['content'], true);
        }

        return response()->json([
            'requests' => $requests
        ]);
    }

    /**
     * Удаление подписки по $request->request_id
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $requestId = $request->post('request_id');
        RequestModel::where(['id' => $requestId])->delete();

        return response()->json([
            'response' => 'ok'
        ]);
    }

    /**
     * Получение подписки
     *
     * @param  $request_id
     * @return JsonResponse
     */
    public function get_data($request_id): JsonResponse
    {
        $request = RequestModel::find($request_id);

        if ($request['content'] ?? false) {
            $request['content'] = json_decode($request['content']);
        }

        if ($request['output'] ?? false) {
            $request['output'] = json_decode($request['output']);
        }

        // if ($request['output']->config->content ?? false) {
        //     $request['output']->config->content = json_decode($request['output']->config->content);
        // }

        return response()->json([
            'data' => $request
        ]);
    }

    /**
     * Формирования календаря цен
     *
     * @param  Request $request
     * @param  TravelPayoutsServices $travel_payouts_services
     * @return JsonResponse
     */
    public function priceCalendar(Request $request, TravelPayoutsServices $travel_payouts_services): JsonResponse
    {
        return response()->json($travel_payouts_services->priceCalendar($request->origin, $request->destination));
    }

    /**
     * Формирование данных по подписке
     *
     * @param  int $id
     * @return JsonResponse
     */
    public function requestAviabot(int $id): JsonResponse
    {
        $response = Http::get('https://back.aviabot.app/get-request/' . $id)->json();

        $response['data']['content'] = json_decode($response['data']['content']);
        $response['data']['output'] = json_decode($response['data']['output']);

        if ($response['data']['output']->config->content ?? false) {
            $response['data']['output']->config->content = json_decode($response['data']['output']->config->content);
        }

        return response()->json($response);
    }

    /**
     * Поиск билетов по заданным характеристикам
     *
     * @param  Request $request
     * @param  TravelPayoutsServices $travel_payouts_services
     * @return JsonResponse
     */
    public function searchTickets(Request $request, TravelPayoutsServices $travel_payouts_services): JsonResponse
    {
        return response()->json($travel_payouts_services->searchResults($travel_payouts_services->searchTickets($request), 5, 5));
    }

    /**
     * Поиск билетов по search_id
     *
     * @param  Request $request
     * @param  TravelPayoutsServices $travel_payouts_services
     * @return JsonResponse
     */
    public function searchResult(Request $request, TravelPayoutsServices $travel_payouts_services): JsonResponse
    {
        return response()->json($travel_payouts_services->searchResults($request->search_id, 25, 25));
    }

    /**
     * Формирование партнёрской ссылки на покупку найденного билета
     *
     * @param  Request $request
     * @param  TravelPayoutsServices $travel_payouts_services
     * @return JsonResponse
     */
    public function getURL(Request $request, TravelPayoutsServices $travel_payouts_services): JsonResponse
    {
        return response()->json($travel_payouts_services->getURL($request->search_id, $request->terms_url) ?? []);
    }

    /**
     * Отправка первого n количества билетов в личные сообщения пользователя
     *
     * @param  Request $request
     * @param  VkApi $vk_api
     * @param  FormationMessageServices $formation_message_services
     * @return JsonResponse
     */
    public function sendFirstSearchTickets(Request $request, VkApi $vk_api, FormationMessageServices $formation_message_services): JsonResponse
    {
        $messages = $formation_message_services->sendFirstSearchTickets($request->src, $request->dst, $request->bullets, $request->airlines, $request->search_id);

        foreach ($messages as $message) {
            $response[] = $vk_api->messagesSend(['user_id' => $request->user_id], $message['message'], env('SEND_FIRST_SEARCH_VK_PUBLIC_ID', '204613902'), false, $message['keyboard']);
        }

        return response()->json($response ?? []);
    }
}
