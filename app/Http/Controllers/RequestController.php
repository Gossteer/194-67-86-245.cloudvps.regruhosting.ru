<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use App\Models\Group;
use Illuminate\Support\Facades\Http;

class RequestController extends Controller
{
    public function all(Request $request)
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

    public function delete(Request $request)
    {
        $requestId = $request->post('request_id');
        RequestModel::where(['id' => $requestId])->delete();

        return response()->json([
            'response' => 'ok'
        ]);
    }

    public function get_data($request_id)
    {
        $request = RequestModel::find($request_id);

        if ($request['content'] ?? false) {
            $request['content'] = json_decode($request['content']);
        }

        if ($request['output'] ?? false) {
            $request['output'] = json_decode($request['output']);
        }

        if ($request['output']->config->content ?? false) {
            $request['output']->config->content = json_decode($request['output']->config->content);
        }

        return response()->json([
            'data' => $request
        ]);
    }

    public function priceCalendar(Request $request)
    {
        return response()->json(Http::withHeaders([
            'x-access-token' => config('app.token_calendar'),
        ])->get('https://api.travelpayouts.com/v1/prices/calendar', [
            'origin' => $request->origin,
            'destination' => $request->destination,
            'calendar_type' => 'departure_date'
        ])->json());
    }

    public function requestAviabot(int $id)
    {
        $response = Http::get('https://back.aviabot.app/get-request/' . $id)->json();

        $response['data']['content'] = json_decode($response['data']['content']);
        $response['data']['output'] = json_decode($response['data']['output']);
        $response['data']['output']->config->content = json_decode($response['data']['output']->config->content);

        return response()->json($response);
    }

    public function searchTickets(Request $request)
    {
        $response = Http::timeout(5)->post('http://api.travelpayouts.com/v1/flight_search', [
            'signature' =>  md5("d378bb3f3b879e6fc87899314ba5ce5d:back.aviabot.app:ru:122890:1:0:0:{$request['date_src']}:{$request['dst']['code']}:{$request['src']['code']}:{$request['date_dst']}:{$request['src']['code']}:{$request['dst']['code']}:Y:{$request->ip()}"),
            "marker" => "122890",
            "host" => "back.aviabot.app",
            "user_ip" => $request->ip(),
            "locale" => "ru",
            "trip_class" => "Y",
            "passengers" => [
                "adults" => 1,
                "children" => 0,
                "infants" => 0
            ],
            "segments" => [
                [
                    "origin" => $request['src']['code'],
                    "destination" =>  $request['dst']['code'],
                    "date" => $request['date_src']
                ],
                [
                    "origin" => $request['dst']['code'],
                    "destination" => $request['src']['code'],
                    "date" => $request['date_dst']
                ]
            ]
        ]);

        if ($response->status() !== 200) {
            return response()->json($response['error'],$response->status());
        }

        $response_search = Http::timeout(20)->withHeaders([
            'Accept-Encoding' => 'gzip, deflate, br',
            'Connection' => 'keep-alive'
        ])->get('http://api.travelpayouts.com/v1/flight_search_results', ['uuid'=>$response['search_id']]);

        $response_search = Http::timeout(20)->withHeaders([
            'Accept-Encoding' => 'gzip, deflate, br',
            'Connection' => 'keep-alive'
        ])->get('http://api.travelpayouts.com/v1/flight_search_results', ['uuid'=>$response['search_id']]);

        $response_search = Http::timeout(20)->withHeaders([
            'Accept-Encoding' => 'gzip, deflate, br',
            'Connection' => 'keep-alive'
        ])->get('http://api.travelpayouts.com/v1/flight_search_results', ['uuid'=>$response['search_id']]);

        if ($response_search->status() !== 200) {
            return response()->json($response_search['error'],$response_search->status());
        }

        return response()->json($response_search->json());
    }
}
