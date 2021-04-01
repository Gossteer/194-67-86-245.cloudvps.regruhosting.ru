<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function save(Request $request)
    {
        Chat::createNewOne($request->id, $request->token);

        return response()->json([
            'response' => 'ok'
        ]);
    }

    public function list()
    {
        return Chat::pluck('id');
    }
}
