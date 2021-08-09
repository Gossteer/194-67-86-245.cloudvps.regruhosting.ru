<?php

namespace Tests\Feature;

use App\Models\Request;
use App\Models\User;
use App\Services\VkApi;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $api;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->api = new VkApi();
    }

    public function testRequestMessageReceive()
    {
        $this->post('/save-user-api-request', [
            'user_id' => 448030989,
            'id' => 99991,
            'content' => [
                "src.CountryCode" => "RU",
                "Discount" => [
                    '$gte' => 30
                ],
                "SrcDayOfWeek" => [
                    '$in' => [0, 1, 2, 3, 4, 5, 6]
                ],
                "Price" => [
                    '$gte' => 100,
                    '$lte' => 500000
                ],
                "Temp.Max" => [
                    '$gte' => -50,
                    '$lte' => 50
                ],
                "Temp.Min" => [
                    '$gte' => -50,
                    '$lte' => 50
                ],
                "Dst.continentCode" => "E"
            ],
            'interval' => 60,
            'updated' => 99999,
            'limit' => 10,
            'output' => [
                "citySrc" => "\u041d\u043e\u0432\u043e\u0441\u0438\u0431\u0438\u0440\u0441\u043a",
                "countrySrc" => "\u0420\u043e\u0441\u0441\u0438\u044f",
                "continentDst" => "E",
                "costMax" => 500000,
                "costMin" => 100,
                "currency" => "RUB",
                "currencyForUrl" => "RUB",
                "language" => "ru",
                "randomCost" => true,
                "forward" => true,
                "dual" => true,
                "passengers" => "url",
                "updated" => 60,
                "limit" => "0",
                "interval" => "60",
                "optional" => true,
                "dates" => [
                    [
                        "year" => 2020,
                        "season" => "all",
                        "month" => "0"
                    ]
                ],
                "showAddionalWeather" => true,
                "profitability" => "30",
                "nightTemp.max" => 50,
                "nightTemp.min" => -50,
                "dayTemp.max" => 50,
                "dayTemp.min" => -50,
                "days" => [
                    true, true, true, true, true, true, true
                ]
            ]
        ]);

        $this->assertDatabaseHas('requests', [
            'id' => 99991,
        ]);

        $request = Request::query()->find(99991);
        $user = User::query()->find(448030989);
        foreach ($request->getFlightsFromApi() as $flight) {
            if (!$user->hasRequestReceived($flight['id']) && $request->inRequestAllowableUpdatedDatesRadius($flight)) {
                sleep(15);
                $this->api->messagesSend(
                    ['user_id' => $user->id],
                    $request->makeRequestMessage($flight),
                    config('vk.groups.MIX_MAIN_VK_PUBLIC_ID'),
                    $this->getKeyboard($request->getUrl($flight))
                );
                $user->receivedRequest($flight['id']);
            }
        }

        $this->assertIsObject($request);
    }

    private function getKeyboard($url = null)
    {
        if (isset($url)) {
            return [
                'one_time' => false,
                'inline' => true,
                "buttons" => [[
                    [
                        "action" => [
                            'type' => "open_link",
                            'link' => $url,
                            "label" => "Проверить цену"
                        ]
                    ],
                ]]
            ];
        }

        return null;
    }
}
