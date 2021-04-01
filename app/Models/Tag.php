<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'tags';

    protected $fillable = [
        'body', 'group_id', 'group_name', 'category', 'section'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'users_tags');
    }

    public static function getUserTagsByCertainTagOrTagForAll($user, $match, $groupId)
    {
        return $user->tags()->where([
            ['body', '=', $match],
            ['group_id', '=', $groupId]
        ])->orWhere([
            ['body', '=', '#allOut@cruise_all'],
            ['group_id', '=', $groupId]
        ])->first();
    }
}
