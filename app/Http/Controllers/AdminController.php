<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function save(Request $request)
    {
        $user = Message::where(['name' => 'hello_text'])->first() ?? new Message();
        $user->name = 'hello_text';
        $user->content = $request->hello_text;
        $user->save();

        $user = Message::where(['name' => 'api_text'])->first() ?? new Message();
        $user->name = 'api_text';
        $user->content = strip_tags($request->api_text);
        $user->save();

        $user = Message::where(['name' => 'partner_marker'])->first() ?? new Message();
        $user->name = 'partner_marker';
        $user->content = strip_tags($request->partner_marker);
        $user->save();

        return response()->json([
            'response' => 'ok'
        ]);
    }
}
