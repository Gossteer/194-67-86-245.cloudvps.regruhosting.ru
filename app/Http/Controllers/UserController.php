<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;


class UserController extends Controller
{
    public function saveRequest(Request $request)
    {		
        $model = User::attachRequestToUser($request->post('user_id'), $request);

        return response()->json([
            'response' => 'ok',
            'id' => $model->id,
        ]);
    }
}
