<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Не используется
class UserReceivedRequest extends Model
{
    /**
     * Выбор таблицы
     *
     * @var string
     */
    protected $table = 'users_received_requests';

    /**
     * Атрибуты, которые можно назначать массово назначать
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'request_api_id',
        'subscription_category_id'
    ];

    public function subscriptionCategory()
    {
        return $this->belongsTo(SubscriptionCategory::class);
    }
}
