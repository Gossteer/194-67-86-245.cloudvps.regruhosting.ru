<?php

namespace App\Services;

use App\Models\Message;
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class FormationMessageServices
{
    private TravelPayoutsServices $travel_payouts_services;

    public function __construct(TravelPayoutsServices $travel_payouts_services)
    {
        $this->travel_payouts_services = $travel_payouts_services;
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
                $group_buttons[$group][] = $this->getActionForButtons($group, $value);
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

    public function sendFirstSearchTickets(array $src, array $dst, array $bullets, array $airlines, string $search_id): array
    {
        $data['srcCity'] = $src['name'];
        $data['srcCountry'] = $src['country_name'];
        $data['dstCity'] = $dst['name'];
        $data['dstCountry'] = $dst['country_name'];

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
            $data['updatedD'] = date('d.m.Y H:i');

            $response[$key]['message'] = $this->makeRequestMessage($data, 'api_send_tickets');
            if ($url = ($this->travel_payouts_services->getURL($search_id, $terms['url'])['url'] ?? null)) {
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

    public function sendSubscriptionSearchTickets(array $src, array $dst, array $bullet, array $airlines, string $search_id): array
    {
        $data['srcCity'] = $src['name'];
        $data['srcCountry'] = $src['country_name'];
        $data['dstCity'] = $dst['name'];
        $data['dstCountry'] = $dst['country_name'];

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
        $data['price_diff'] = abs(($price_diff = $bullet['old_price'] - $terms['price']));
        $data['condition'] = (($price_diff < 0) ? 'увеличилась' : 'снизилась');
        $data['old_price'] = $bullet['old_price'];
        $data['updatedD'] = date('d.m.Y H:i');

        $response['message'] = $this->makeRequestMessage($data, 'send_new_price_subscription');
        if ($url = ($this->travel_payouts_services->getURL($search_id, $terms['url'])['url'] ?? null)) {
            $response['keyboard'] = $this->makeRequestKeyboard(false, true, [
                'open_link' => [
                    'link' => $url,
                    'label' => 'Купить за 15 минут'
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

            default:
                $error_message = ("Тип $type не найден" . ' getActionForButtons   ' . __FILE__ . ' строка:' . __LINE__);
                Log::error($error_message);
                throw new Exception($error_message);
                break;
        }
    }
}
