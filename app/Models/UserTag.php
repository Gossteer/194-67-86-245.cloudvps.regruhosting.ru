<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTag extends Model
{
    protected $table = 'users_tags';

    protected $fillable = [
        'user_id', 'tag_id'
    ];
}

