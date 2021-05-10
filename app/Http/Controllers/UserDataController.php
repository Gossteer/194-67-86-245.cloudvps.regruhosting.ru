<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserDataRequest;
use App\Models\User;
use App\Models\UserData;
use App\Services\VkApi;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserDataController extends Controller
{
    //public function store(Request $request, User $user, $key)
	public function store(Request $request, $user_id, $key, VkApi $vkApi)
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

    public function get(Request $request, $user_id)
    {
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
