<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

class TravelPayoutsServices
{
    private Client $clien;

    public function __construct() {
        $this->clien = new Client();
    }

    public function searchTickets(Request $request): array
    {
        session_write_close();

        session_start();
        set_time_limit(50);
        ini_set('memory_limit', '-1');

        $date_dst = $request['date_dst'] ?? null;
        $trip_class = $request['trip_class'] ?? "Y";
        $passengers = [
            'adults' => $request['passengers']['adults'] ?? 1,
            'children' => $request['passengers']['children'] ?? 0,
            'infants' => $request['passengers']['infants'] ?? 0,
        ];

        if ($date_dst) {
            $response = Http::timeout(5)->post('http://api.travelpayouts.com/v1/flight_search', [
                'signature' =>  md5("d378bb3f3b879e6fc87899314ba5ce5d:back.aviabot.app:ru:122890:{$passengers['adults']}:{$passengers['children']}:{$passengers['infants']}:{$request['date_src']}:{$request['dst']['code']}:{$request['src']['code']}:$date_dst:{$request['src']['code']}:{$request['dst']['code']}:$trip_class:{$request->ip()}"),
                "marker" => "122890",
                "host" => "back.aviabot.app",
                "user_ip" => $request->ip(),
                "locale" => "ru",
                "trip_class" => $request['trip_class'] ?? "Y",
                "passengers" => [
                    "adults" => $request['passengers']['adults'] ?? 1,
                    "children" => $request['passengers']['children'] ?? 0,
                    "infants" => $request['passengers']['infants'] ?? 0
                ],
                "segments" => [
                    [
                        "origin" => $request['src']['code'],
                        "destination" =>  $request['dst']['code'],
                        "date" => $request['date_src']
                    ],
                    [
                        "origin" => $request['dst']['code'],
                        "destination" => $request['src']['code'],
                        "date" => $date_dst
                    ]
                ]
            ]);
        } else {
            $response = Http::timeout(5)->post('http://api.travelpayouts.com/v1/flight_search', [
                'signature' =>  md5("d378bb3f3b879e6fc87899314ba5ce5d:back.aviabot.app:ru:122890:{$passengers['adults']}:{$passengers['children']}:{$passengers['infants']}:{$request['date_src']}:{$request['dst']['code']}:{$request['src']['code']}:$trip_class:{$request->ip()}"),
                "marker" => "122890",
                "host" => "back.aviabot.app",
                "user_ip" => $request->ip(),
                "locale" => "ru",
                "trip_class" => $request['trip_class'] ?? "Y",
                "passengers" => [
                    "adults" => $request['passengers']['adults'] ?? 1,
                    "children" => $request['passengers']['children'] ?? 0,
                    "infants" => $request['passengers']['infants'] ?? 0
                ],
                "segments" => [
                    [
                        "origin" => $request['src']['code'],
                        "destination" =>  $request['dst']['code'],
                        "date" => $request['date_src']
                    ]
                ]
            ]);
        }



        if ($response->status() !== 200) {
            return response()->json($response['error'], $response->status());
        }

        $_SESSION['search_id'] =  $response['search_id'];

        $response = $this->clien->getAsync('http://api.travelpayouts.com/v1/flight_search_results?uuid=' . $response['search_id'], [
            'timeout' => 10,
            'read_timeout' => 10,
            'connect_timeout' => 10,
        ])->then(
            function ($response) {
                return $response->getBody();
            },
            function ($exception) {
                return $exception->getMessage();
            }
        );

        sleep(8);

        $_SESSION['response_result'] = $response->wait()->getContents();

        session_write_close();

        $response_result = json_decode($_SESSION['response_result'], true);

        usort($response_result, function ($value, $value_next) {
            if (isset($value['filters_boundary']['price']['min'], $value_next['filters_boundary']['price']['min'])) {
                if ($value['filters_boundary']['price']['min'] == $value_next['filters_boundary']['price']['min']) {
                    return 0;
                }
                return ($value['filters_boundary']['price']['min'] < $value_next['filters_boundary']['price']['min']) ? -1 : 1;
            }
        });

        return [
            'response_result' => $response_result,
            'search_id' => $_SESSION['search_id']
            ];
    }

    public function priceCalendar($origin, $destination, string $calendar_type = 'departure_date'): ?array
    {
        return Http::withHeaders([
            'x-access-token' => config('app.token_calendar'),
        ])->get('https://api.travelpayouts.com/v1/prices/calendar', [
            'origin' => $origin,
            'destination' => $destination,
            'calendar_type' => $calendar_type
        ])->json();
    }

    public function getURL($search_id, $terms_url): ?array
    {
        return Http::get('http://api.travelpayouts.com/v1/flight_searches/' . $search_id . '/clicks/' . $terms_url . '.json?marker=122890')->json();
    }
}
