<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'origin_code',
        'destination_code',
        'origin_date',
        'destination_date',
        'data',
        'last_data_response',
        'last_date',
        'user_id',
        'period',
        'subscription_category_id'
    ];

    protected $casts = [
        'data' => 'collection',
        'last_data_response' => 'collection',
    ];

    public function subscriptionCategory()
    {
        return $this->belongsTo(SubscriptionCategory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
