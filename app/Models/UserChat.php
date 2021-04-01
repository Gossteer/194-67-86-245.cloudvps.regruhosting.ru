<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserChat extends Model
{
    protected $table = 'user_chats';

    protected $fillable = [
        'user_id', 'chat_id'
    ];

    public function chat()
    {
        return $this->hasOne(Chat::class, 'id', 'chat_id');
    }
}

