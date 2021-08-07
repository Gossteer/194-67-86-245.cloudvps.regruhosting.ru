<?php

namespace App\Models;

use App\Exceptions\Models\UserException;
use App\Models\UserData;
use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * Атрибуты, которые можно назначать массово назначать
     *
     * @var array
     */
    protected $fillable = [
        'id'
    ];

    /**
     * Связь многие ко многим
     *
     * @return belongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'users_groups');
    }

    /**
     * Связь многие ко многим
     *
     * @return belongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'users_tags');
    }

    /**
     * Связь многие ко многим с группами(чатами)
     *
     * @return belongsToMany
     */
    public function chats()
    {
        return $this->belongsToMany(Chat::class, 'user_chats');
    }

    /**
     * Связь один ко многим с подписками
     *
     * @return hasMany
     */
    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    /**
     * Связь один ко многим с полученными подписками
     *
     * @return hasMany
     */
    public function userReceivedRequest()
    {
        return $this->hasMany(UserReceivedRequest::class);
    }

    /**
     * Сохранение информации о получение юзером подписки
     *
     * @param int $requestApiId
     * @return void
     */
    public function receivedRequest($requestApiId, ?int $subscription_category_id = null)
    {
        $requestReceived = new UserReceivedRequest();
        $requestReceived->request_api_id = $requestApiId;
        $requestReceived->subscription_category_id = $subscription_category_id;
        $this->userReceivedRequest()->save($requestReceived);
    }

    /**
     * Проверка на получение пользователем подписки
     *
     * @param int $flightId
     * @return UserReceivedRequest
     */
    public function hasRequestReceived($flightId)
    {
        return $this->userReceivedRequest()->where(['request_api_id' => $flightId])->first();
    }

    /**
     * Получение/создание чата(группы) пользователя
     *
     * @param int $groupId
     * @return Chat
     */
    public function getChatOrCreateNew($groupId)
    {
        $chat = null;
        if ($groupId) {
            $chat = $this->chats()->where([['chat_id', '=', $groupId]])->first();
        }

        if (!$chat) {
            $chat = Chat::find($groupId);
            if ($chat) {
                $this->chats()->save($chat);
            } else {
                Log::error("Please create chat for {$groupId} in admin panel");
                //throw new UserException("Please create chat for {$groupId} in admin panel");
            }
        }

        return $chat;
    }

    /**
     * Создание нового пользователя по id
     *
     * @param int $id
     * @return User
     */
    public static function createNewOneById($id)
    {
        $user = new self();
        $user->id = $id;
        $user->save();

        return $user;
    }

    /**
     * Прикрепление пользователю подписки
     *
     * @param int $userId
     * @param Request $request
     * @return bool
     */
    public static function attachRequestToUser($userId, $request)
    {
        $user = User::firstOrCreate(['id' => $userId]);
        $requestModel = Request::firstOrNew(['id' => $request->post('id')]);
        $requestModel->id = $request->post('id');
        $requestModel->content = json_encode($request->post('content'));
        $requestModel->interval = $request->post('interval');
        $requestModel->updated = $request->post('updated');
        $requestModel->limit = $request->post('limit');
        $requestModel->output = json_encode($request->post('output'));
        $requestModel->created_at = Carbon::now();
        $requestModel->updated_at = Carbon::now()->subYears(1);
        $requestModel->timestamps = false;
        $requestModel->group_id = $request->post('group_id');
        $requestModel->send_count = $request->send_count ?? $requestModel->send_count ?? 0;

        return $user->requests()->save($requestModel);
    }

    /**
     * Связь один ко многим с данными(настройками пользователя)
     *
     * @return hasMany
     */
    public function data()
    {
        return $this->hasMany(UserData::class);
    }
}
