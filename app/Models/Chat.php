<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    /**
     * Выбор таблицы
     *
     * @var string
     */
    protected $table = 'chats';

    /**
     * Временные метки
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Атрибуты, которые можно назначать массово назначать
     *
     * @var array
     */
    protected $fillable = [
        'token'
    ];

    /**
     * Создание или выбор чата
     *
     * @param int $id
     * @param $token
     * @return Chat
     */
    public static function createNewOne(int $id, $token): Chat
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
