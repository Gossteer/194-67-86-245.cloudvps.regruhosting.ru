<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Request;
use App\Models\Group;
use App\Models\Chat;

class TestController extends Controller
{
    public function test()
    {
		$userId = 768721;
		/*$groupId = 198318491;
		$isAllowed = false;
		$user = User::find($userId);
		if ($groupId) {
			if ($user) {
				$chat = $user->chats()->where([['chat_id', '=', $groupId]])->first();
				if ($chat) {
					$data = file_get_contents("https://api.vk.com/method/messages.isMessagesFromGroupAllowed?access_token=".$chat->token."&group_id=".$groupId."&user_id=".$userId."&v=5.126");
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

		die;*/
		//$userId = 382960669;

        foreach (Request::getAllByUserId($userId) as $request) {
			print_r($request);
            foreach ($request->getFlightsFromApi() as $flight) {
                print_r($flight);
            }
        }

		/*$group_id = 198318499;

		echo Group::getGroupType($group_id);*/
    }
}
