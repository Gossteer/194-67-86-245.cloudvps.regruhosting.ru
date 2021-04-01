<?php

namespace App\Services;

use App\Models\Tag;
use App\Models\User;
use App\Models\UserChat;
use Illuminate\Support\Facades\Cache;

class VkGroupEventsListener
{
    const JOIN_TYPE_JOIN = 'join';

    const COLOR_POSITIVE = 'positive';
    const COLOR_NEGATIVE = 'negative';

    const CALLBACK_API_EVENT_CONFIRMATION = 'confirmation';
    const CALLBACK_API_EVENT_GROUP_JOIN = 'group_join';
    const CALLBACK_API_EVENT_WALL_POST_NEW = 'wall_post_new';
    const CALLBACK_API_EVENT_MESSAGE_ALLOW = 'message_allow';

    private $api;

    public function __construct()
    {
        $this->api = new VkApi();
    }

    public function recognizeEvent()
    {
        $event = $this->getEvent();
        switch ($event['type']) {
            case self::CALLBACK_API_EVENT_CONFIRMATION:
                $this->sendConfirmation();
                break;
            case self::CALLBACK_API_EVENT_WALL_POST_NEW:
                $this->newWallPost($event);
                break;
            case self::CALLBACK_API_EVENT_MESSAGE_ALLOW:
                $this->subscribeOnPosts($event);
                break;
            default:
                vkResponse('Unsupported event');
                break;
        }
    }

    private function getEvent()
    {
        return json_decode(file_get_contents('php://input'), true);
    }

    private function sendConfirmation()
    {
        $value = Cache::get('confirmation_code');

        vkResponse($value);
    }

    public function newWallPost($event)
    {
        $groupId = $event['group_id'];
        $data = $event['object'];
        $matches = $this->findMatchesFromWallPost($data);

        //\Log::info('post', $event);
        //\Log::info('matches', $matches);

        if (empty($matches)) {
            return;
        }

        return $this->sendMessagesForUsersByTagsMatch($matches, $data, $groupId);
    }

    private function findMatchesFromWallPost($data)
    {
        $matches = [];

        preg_match_all('/(#.*@.*)/', $data['text'], $matches);

        if (!empty($matches)) {
            $matches = $matches[1];
        }

        return $matches;
    }

    private function sendMessagesForUsersByTagsMatch($matches, $data, $groupId)
    {
        $users = User::all();
        foreach ($users as $user) {
            foreach ($matches as $tag) {
                if (Tag::getUserTagsByCertainTagOrTagForAll($user, trim($tag), $groupId)) {
                    if ($this->api->messagesSend(['user_id' => $user->id], $data['text'], $groupId, false)) {
                        break;
                    }
                }
            }
        }
    }

    public function subscribeOnPosts($event)
    {

        $userId = $event['object']['user_id'];
        $chatId = $event['group_id'];
        if ($userId && $chatId) {
            $user = User::find($userId);

            return $user->getChatOrCreateNew($chatId);
        }
    }
}
