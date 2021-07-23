<?php

namespace App\Services;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class VkApi
{
    /**
     * Параметры запроса
     *
     * @var array
     */
    private $params = [];
    /**
     * GuzzleHttp клиент
     *
     * @var Client
     */
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Отправка сообщения
     *
     * @param array $data
     * @param string $message
     * @param int $groupId
     * @param bool $hasAttachment
     * @param string|null $keyboard
     * @return array|null
     */
    public function messagesSend($data, $message, $groupId = null, $hasAttachment = true, ?string $keyboard = null)
    {

        $user = User::query()->find($data['user_id']);
        $chat = $user->getChatOrCreateNew($groupId);
        if (!$chat) {
            Log::error('Token not found for ' . $groupId, $data);
            return;
        }

        $this->prepareMessageData($data, $groupId, $message, $hasAttachment, $keyboard);

        return $this->call(
            'messages.send',
            $chat->token
        );
    }

    /**
     * Подготовка тела запроса
     *
     * @param array $data
     * @param string $message
     * @param int $groupId
     * @param bool $hasAttachment
     * @param string|null $keyboard
     * @return void
     */
    public function prepareMessageData($data, $groupId, $message, $hasAttachment, ?string $keyboard = null)
    {
        $arr = [
            'user_id' => $data['from_id'] ?? $data['user_id'],
            'group_id' => $groupId,
            'random_id' => rand(),
            'domain' => 'App',
            'message' => $message,
            'title' => 'Дешевые авиабилеты',
            'dont_parse_links' => 0,
        ];

        if (isset($keyboard)) {
            $arr['keyboard'] = $keyboard;
        }

        if ($hasAttachment) {
            $arr['attachment'] = 'https://vk.com/app7584642_451051929';
        }

        $this->params = array_merge(
            $this->params,
            $arr
        );
    }

    /**
     * Отправка сообщения
     *
     * @param string $method
     * @param string $accessToken
     * @return array|null
     */
    private function call($method, $accessToken = '')
    {
        $this->params['access_token'] = $accessToken;
        $this->params['v'] = getenv('VK_API_VERSION');

        // Log::info(json_encode($this->params));

        $response = $this->client->post(
            getenv('VK_API_ENDPOINT') . $method,
            [
                'form_params' => $this->params
            ]
        );

        Log::info('response' . $response->getBody()->getContents());
        $result = json_decode($response->getBody()->getContents(), true);

        return $result['response'] ?? null;
    }
}
