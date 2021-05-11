<?php

namespace App\Models;

use App\Exceptions\Models\UserException;
use App\Models\UserData;
use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'id'
    ];

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'users_groups');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'users_tags');
    }

    public function chats()
    {
        return $this->belongsToMany(Chat::class, 'user_chats');
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public function userReceivedRequest()
    {
        return $this->hasMany(UserReceivedRequest::class);
    }

    public function receivedRequest($requestApiId)
    {
        $requestReceived = new UserReceivedRequest();
        $requestReceived->request_api_id = $requestApiId;
        $this->userReceivedRequest()->save($requestReceived);
    }

    public function hasRequestReceived($flightId)
    {
        return $this->userReceivedRequest()->where(['request_api_id' => $flightId])->first();
    }

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
                \Log::error("Please create chat for {$groupId} in admin panel");
                //throw new UserException("Please create chat for {$groupId} in admin panel");
            }
        }

        return $chat;
    }

    public static function createNewOneById($id)
    {
        $user = new self();
        $user->id = $id;
        $user->save();

        return $user;
    }

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

    public function data()
    {
        return $this->hasMany(UserData::class);
    }
}
