<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Не используется
class UserTag extends Model
{
    /**
     * Выбор таблицы
     *
     * @var string
     */
    protected $table = 'users_tags';

    /**
     * Атрибуты, которые можно назначать массово назначать
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'tag_id'
    ];
}
