<?php

namespace App\Services;

use App\Models\Message;
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class FormationMessageServices
{
    public function prepareMessageDataVkApi2(string $text, int $peer_id, string $token, string $version, ?string $keyboard = null): array
    {
        return [
            'random_id' => rand(),
            'message' => $text,
            'peer_id' => $peer_id,
            'access_token' => $token,
            'v' => $version,
            'keyboard' => $keyboard
        ];
    }

    public function prepareUrlVkApi2(string $endpoint, array $params): string
    {
        return "https://api.vk.com/method/$endpoint?" . http_build_query($params);
    }

    public function makeRequestMessage(array $data, string $type_message): string
    {
        $healthy = [];
        $yummy = [];
        foreach ($data as $key => $value) {
            $healthy[] = "[[$key]]";
            $yummy[] = $value;
        }

        $apiMessage = Message::select('content')->where(['name' => $type_message])->first();

        return str_replace($healthy, $yummy, $apiMessage->content);
    }

    public function makeRequestKeyboard(bool $one_time = false, bool $inline = false, array $buttons): ?string
    {
        try {
            foreach ($buttons as $group => $value) {
                if (isset($value[0])) {
                    foreach ($value as $key => $button) {
                        $group_buttons[$group][] = $this->getActionForButtons($group, $button);
                    }
                } else {
                    $group_buttons[$group][] = $this->getActionForButtons($group, $value);
                }
            }

            foreach ($group_buttons as $group => $button) {
                $keyboard_buttons[] = $button;
            }

            return json_encode([
                'one_time' => $one_time,
                'inline' => $inline,
                "buttons" => $keyboard_buttons
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            Log::error(($e->getMessage() . ', ' . $e->getFile() . ', строка:' . $e->getLine()), 'error', $e->getTrace());
            return null;
        }
    }

    public function sendHelloMessage(): array
    {
        $data['time_tostr'] = self::getHelloMessageForTime();

        $response['message'] = $this->makeRequestMessage($data, 'hello_text');

        return $response;
    }

    public function sendFirstSearchTickets(array $src, array $dst, array $bullets, array $airlines, string $search_id): array
    {
        $data = [];
        $this->setCityAndContry($src, $dst, $data);

        foreach ($bullets as $key => $bullet) {

            if ($bullet_segment_dst = ($bullet['segment'][1] ?? false)) {
                $data['arrow'] = '⇄';
                $data['dates'] = date('d.m.Y', strtotime($bullet['segment'][0]['flight'][0]['departure_date'])) . ' ' . $bullet['segment'][0]['flight'][0]['arrival_time'] . ' - ' . date('d.m.Y', strtotime($bullet_segment_dst['flight'][0]['departure_date'])) . ' ' . $bullet_segment_dst['flight'][0]['arrival_time'];
                $data['footer'] = 'Туда: ' .  $airlines[$bullet['segment'][0]['flight'][0]['operating_carrier']]['name'] . ', обратно: ' . $airlines[$bullet_segment_dst['flight'][0]['operating_carrier']]['name'];
            } else {
                $data['arrow'] = '→';
                $data['dates'] = date('d.m.Y', strtotime($bullet['segment'][0]['flight'][0]['departure_date'])) . ' ' . $bullet['segment'][0]['flight'][0]['arrival_time'];
                $data['footer'] = 'Туда: ' . $airlines[$bullet['segment'][0]['flight'][0]['operating_carrier']]['name'];
            }

            $data['seller'] = current($bullet['gates_info'])['label'];

            $terms = array_shift($bullet['terms']);
            $data['price'] = $terms['price'];
            $data['time_tostr'] = self::getHelloMessageForTime();
            $data['updatedD'] = date('d.m.Y H:i');

            $response[$key]['message'] = $this->makeRequestMessage($data, 'api_send_tickets');
            $travel_payouts_services = new TravelPayoutsServices();
            if ($url = ($travel_payouts_services->getURL($search_id, $terms['url'])['url'] ?? null)) {
                $response[$key]['keyboard'] = $this->makeRequestKeyboard(false, true, [
                    'open_link' => [
                        'link' => $url,
                        'label' => 'Проверить цену'
                    ]
                ]);
            } else {
                $response[$key]['keyboard'] = $url;
            }
        }

        return $response;
    }

    public function sendAfterDeleteSub(array $src, array $dst, array $bullet): array
    {
        $data = [];
        $this->setCityAndContry($src, $dst, $data);

        if ($bullet_segment_dst = ($bullet['segment'][1] ?? false)) {
            $data['arrow'] = '⇄';
            $data['dates'] = date('d.m.Y', strtotime($bullet['segment'][0]['flight'][0]['departure_date'])) . ' в ' . $bullet['segment'][0]['flight'][0]['arrival_time'] . ' - ' . date('d.m.Y', strtotime($bullet_segment_dst['flight'][0]['departure_date'])) . ' в ' . $bullet_segment_dst['flight'][0]['arrival_time'];
        } else {
            $data['arrow'] = '→';
            $data['dates'] = date('d.m.Y', strtotime($bullet['segment'][0]['flight'][0]['departure_date'])) . ' в ' . $bullet['segment'][0]['flight'][0]['arrival_time'];
        }
        $data['time_tostr'] = self::getHelloMessageForTime();
        $response['message'] = $this->makeRequestMessage($data, 'send_after_delete_sub');

        return $response;
    }

    private function setCityAndContry(array $src, array $dst, array &$data): void
    {
        $data['srcCity'] = $src['name'];
        $data['srcCountry'] = $src['country_name'];
        $data['dstCity'] = $dst['name'];
        $data['dstCountry'] = $dst['country_name'];
    }

    public function sendSubscriptionSearchTickets(array $src, array $dst, array $bullet, array $airlines, string $search_id, array $passengers, string $trip_class, string $date_create, int $first_price, array $airports): array
    {
        $data = [];
        $this->setCityAndContry($src, $dst, $data);

        if ($transfers_to = ($bullet['segment'][0]['transfers'] ?? false)) {
            $transfers_to_message = "c пересадками в ";
            $transfers_to_key_last = array_key_last($transfers_to);
            foreach ($transfers_to as $key => $value) {
                $transfers_to_message .= $airports[$value['to']]['name'] . (($transfers_to_key_last != $key) ? ", " : " ");
            }
        } else {
            $transfers_to_message = "без пересадок ";
        }

        if ($bullet_segment_dst = ($bullet['segment'][1] ?? false)) {
            if ($transfers_from = ($bullet_segment_dst['transfers'] ?? false)) {
                $transfers_from_message = "c пересадками в ";
                $transfers_from_key_last = array_key_last($transfers_from);
                foreach ($transfers_from as $key => $value) {
                    $transfers_from_message .= $airports[$value['to']]['name'] . (($transfers_from_key_last != $key) ? ", " : " ");
                }
            } else {
                $transfers_from_message = "без пересадок ";
            }

            $data['arrow'] = '⇄';
            $data['dates'] = date('d.m.Y', strtotime($bullet['segment'][0]['flight'][0]['departure_date'])) . ' в ' . $bullet['segment'][0]['flight'][0]['arrival_time'] . ' - ' . date('d.m.Y', strtotime($bullet_segment_dst['flight'][0]['departure_date'])) . ' в ' . $bullet_segment_dst['flight'][0]['arrival_time'];
            $data['footer'] = 'Туда: ' . $transfers_to_message .  $airlines[$bullet['segment'][0]['flight'][0]['operating_carrier']]['name'] . ', обратно: ' . $transfers_from_message . $airlines[$bullet_segment_dst['flight'][0]['operating_carrier']]['name'];
        } else {
            $data['arrow'] = '→';
            $data['dates'] = date('d.m.Y', strtotime($bullet['segment'][0]['flight'][0]['departure_date'])) . ' в ' . $bullet['segment'][0]['flight'][0]['arrival_time'];
            $data['footer'] = 'Туда: ' . $transfers_to_message . $airlines[$bullet['segment'][0]['flight'][0]['operating_carrier']]['name'];
        }

        $data['seller'] = current($bullet['gates_info'])['label'];

        $terms = array_shift($bullet['terms']);
        $data['price'] = $terms['price'];
        $data['first_price'] = $first_price;
        $data['dateCreate'] = date('d.m.Y', strtotime($date_create));
        $data['time_tostr'] = self::getHelloMessageForTime();
        $data['price_diff'] = abs(($price_diff = $bullet['old_price'] - $terms['price']));
        $data['condition'] = (($price_diff < 0) ? 'увеличилась' : 'снизилась');
        $data['old_price'] = $bullet['old_price'];
        $data['updatedD'] = date('d.m.Y в H:i');

        $response['message'] = $this->makeRequestMessage($data, 'send_new_price_subscription');
        $travel_payouts_services = new TravelPayoutsServices();
        if ($url = ($travel_payouts_services->getURL($search_id, $terms['url'])['url'] ?? null)) {
            $response['keyboard'] = $this->makeRequestKeyboard(false, true, [
                'open_link' => [
                    [
                        'link' => $url,
                        'label' => 'Купить'
                    ],
                    [
                        'link' => UserCheapFlightsApiMessagesService::getUrlAviasales($src['code'], $dst['code'], $bullet['segment'][0]['flight'][0]['departure_date'], $passengers, $trip_class, $bullet_segment_dst['flight'][0]['departure_date'] ?? null),
                        'label' => 'Проверить цену'
                    ]
                ]
            ]);
        } else {
            $response['keyboard'] = $url;
        }


        return $response;
    }

    private function getActionForButtons(string $type, array $data): array
    {
        switch ($type) {
            case 'open_link':
                return [
                    "action" =>
                    [
                        'type' => $type,
                        'link' => $data['link'],
                        "label" => $data['label']
                    ]
                ];
                break;
            case 'text':
                return [
                    "action" =>
                    [
                        'type' => $type,
                        'payload' => $data['payload'] ?? null,
                        "label" => $data['label']
                    ],
                    "color" => $data['color'] ?? "secondary"
                ];
                break;

            default:
                $error_message = ("Тип $type не найден" . ' getActionForButtons   ' . __FILE__ . ' строка:' . __LINE__);
                Log::error($error_message);
                throw new Exception($error_message);
                break;
        }
    }

    public static function getHelloMessageForTime(): string
    {
        $date = strtotime(date('H:i'));
        if (strtotime('06:01') <= $date and strtotime('12:00') >= $date) {
            return 'Доброе утро';
        }

        if (strtotime('12:01') <= $date and strtotime('18:00') >= $date) {
            return 'Добрый день';
        }

        if (strtotime('18:01') <= $date and strtotime('24:00') >= $date) {
            return 'Добрый вечер';
        }

        return 'Доброй ночи';
    }
}
