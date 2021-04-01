<?php

namespace Tests\Unit;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Services\VkGroupEventsListener;

class VkGroupEventsListenerTest extends TestCase
{
    use DatabaseTransactions;

    private $listener;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->listener = new VkGroupEventsListener();
    }

    public function testNewWallPostTest()
    {
        $event = [
            'type' => 'wall_post_new',
            'object' =>
                [
                    'id' => 5512,
                    'from_id' => -179104135,
                    'owner_id' => -179104135,
                    'date' => 1598100907,
                    'marked_as_ads' => 0,
                    'post_type' => 'post',
                    'text' => '🏨 Забронируй отель или хостел https://www.booking.com?aid=1540284
✈ Все билеты в
#РиодеЖанейро@cruises_transantlantic
#Бразилия@cruises_transantlantic
✈ Все билеты из
#Рим@cruises_transantlantic
☀ Погода в день прилета ☀ +29°, 🌛 +24°,
🌎 Узнай больше о городе Рио-де-Жанейро https://ru.wikipedia.org/wiki/Рио-де-Жанейро
😊 Все дешевые #авиабилеты сразу',
                    'can_edit' => 1,
                    'created_by' => 448030989,
                    'can_delete' => 1,
                    'comments' =>
                        [
                            'count' => 0,
                        ],
                    'is_favorite' => false,
                ],
            'group_id' => 179104135,
            'event_id' => '1b14719b9964d5369451e645559e37d2bb96bcfe',
        ];
        $response = $this->listener->newWallPost($event);
        $this->assertTrue(is_int($response));
    }

    public function testSubscribeOnPosts()
    {
        $user = User::createNewOneById(123);
        $chat = Chat::createNewOne(1, 'test');
        $event = [
            'object' => [
                'user_id' => $user->id,
            ],
            'group_id' => $chat->id
        ];
        $response = $this->listener->subscribeOnPosts($event);

        $this->assertIsObject($response);
    }
}
