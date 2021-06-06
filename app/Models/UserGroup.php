<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Не используется
class UserGroup extends Model
{
    /**
     * Выбор таблицы
     *
     * @var string
     */
    protected $table = 'users_groups';

    /**
     * Атрибуты, которые можно назначать массово назначать
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'group_id'
    ];
}
