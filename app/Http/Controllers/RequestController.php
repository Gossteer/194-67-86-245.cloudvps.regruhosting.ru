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
		for($i=0; $i<count($requests); $i++) {
			$group_id = $requests[$i]->group_id;
			$data = file_get_contents("https://api.vk.com/method/groups.getById?access_token=6070d8a06070d8a06070d8a03660036322660706070d8a03f3e1814c6491f7856970679&group_id=".$group_id."&v=5.124");
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

	public function get_data($request_id) {
		$request = RequestModel::find($request_id);
        $request['content'] = json_decode($request['content']);
        $request['output'] = json_decode($request['output']);
        $request['output']->config->content = json_decode($request['output']->config->content);

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
}
