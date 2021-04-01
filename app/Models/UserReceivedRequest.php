<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserReceivedRequest extends Model
{
    protected $table = 'users_received_requests';

    protected $fillable = [
        'user_id', 'request_api_id'
    ];
}
