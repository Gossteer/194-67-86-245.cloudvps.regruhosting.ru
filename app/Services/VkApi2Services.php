<?php

namespace App\Services;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class VkApi2Services
{
    /**
     * Токен группы
     *
     * @var string
     */
    private ?string $token;
    /**
     * Версия api
     *
     * @var string
     */
    private ?string $version;
    /**
     * GuzzleHttp клиент
     *
     * @var Client
     */
    private Client $client;

    public function __construct(?string $token = null, ?string $version = null)
    {
        $this->client = new Client();
        $this->token = $token ?? config('vk.api.VK_GROUP_API_TOKEN');
        $this->version = $version ?? config('vk.api.VK_API_VERSION');
    }

    public function prepareMessageData(string $text, int $peer_id): array
    {
        return [
            'message' => $text,
            'peer_id' => $peer_id,
            'access_token' => $this->token,
            'v' => $this->version
        ];
    }

    public function prepareUrl(string $endpoint, array $params): string
    {
        return "https://api.vk.com/method/$endpoint?" . http_build_query($params);
    }

    public function call(string $url): \Psr\Http\Message\ResponseInterface
    {
        return $this->client->get($url);
    }
}
