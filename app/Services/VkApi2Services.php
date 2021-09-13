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
    private string $token;
    /**
     * Версия api
     *
     * @var string
     */
    private string $version;
    /**
     * GuzzleHttp клиент
     *
     * @var Client
     */
    private Client $client;
    /**
     * Сервис форматирования контента
     *
     * @var FormationMessageServices
     */
    private FormationMessageServices $formation_message_services;

    public function __construct(?string $token = null, ?string $version = null)
    {
        $this->client = new Client();
        $this->token = $token ?? config('vk.api.VK_GROUP_API_TOKEN');
        $this->version = $version ?? config('vk.api.VK_API_VERSION');
        $this->formation_message_services = new FormationMessageServices();
    }

    public function prepareMessageData(array $params = [], ?string $keyboard = null): array
    {
        return $this->formation_message_services->prepareMessageDataVkApi2($params, $this->token, $this->version, $keyboard);
    }

    public function prepareUrl(string $endpoint, array $params): string
    {
        return $this->formation_message_services->prepareUrlVkApi2($endpoint, $params);
    }

    public function prepareKeyboard(bool $one_time = false, bool $inline = false, array $buttons): ?string
    {
        return $this->formation_message_services->makeRequestKeyboard($one_time, $inline, $buttons);
    }

    public function call(string $url): \Psr\Http\Message\ResponseInterface
    {
        return $this->client->get($url);
    }
}
