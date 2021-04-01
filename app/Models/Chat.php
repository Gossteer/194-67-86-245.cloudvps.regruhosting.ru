<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $table = 'chats';

    public $timestamps = false;

    protected $fillable = [
        'token'
    ];

    public static function createNewOne($id, $token)
    {
        $chat = Chat::firstOrNew([
            'id' => $id,
        ]);
        $chat->id = $id;
        $chat->token = $token;
        $chat->save();

        return $chat;
    }
}

