<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserChat extends Model
{
    /**
     * Выбор таблицы
     *
     * @var string
     */
    protected $table = 'user_chats';

    /**
     * Атрибуты, которые можно назначать массово назначать
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'chat_id'
    ];

    /**
     * Связь один к одному (к чату/группе)
     *
     * @return hasOne
     */
    public function chat()
    {
        return $this->hasOne(Chat::class, 'id', 'chat_id');
    }
}
