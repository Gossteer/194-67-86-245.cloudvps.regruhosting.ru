<?php

namespace App\Services;

use App\Models\Message;
use Exception;
use Illuminate\Support\Facades\Log;

class FormationMessageServices
{
    private array $healthy;
    private array $yummy;
    private array $buttons;
    private TravelPayoutsServices $travel_payouts_services;

    public function __construct(TravelPayoutsServices $travel_payouts_services)
    {
        $this->travel_payouts_services = $travel_payouts_services;
    }

    public function makeRequestMessage(array $data, string $type_message): string
    {
        foreach ($data as $key => $value) {
            $this->healthy[] = "[[$key]]";
            $this->yummy[] = $value;
        }

        $apiMessage = Message::select('content')->where(['name' => $type_message])->first();

        return str_replace($this->healthy, $this->yummy, $apiMessage->content);
    }

    public function makeRequestKeyboard(bool $one_time = false, bool $inline = false, array $buttons): string
    {
        foreach ($buttons as $group => $value) {
            $this->buttons[$group][] = $this->getActionForButtons($group, $value);
        }

        foreach ($this->buttons as $group => $button) {
            $keyboard_buttons[] = $button;
        }

        return json_encode([
            'one_time' => $one_time,
            'inline' => $inline,
            "buttons" => $keyboard_buttons
        ], JSON_UNESCAPED_UNICODE);
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
                $data['dates'] = date('d.m.Y', strtotime($bullet['segment'][0]['flight'][0]['departure_date'])) . ' - ' . date('d.m.Y', strtotime($bullet_segment_dst['flight'][0]['departure_date']));
                $data['footer'] = 'Туда: ' . 'Обратно';//.  $airlines[$bullet[0]['proposals'][0]['segment'][0]['operating_carrier']]['name'] . ', обратно: ' . $airlines[$bullet_segment_dst['operating_carrier']]['name'];
            } else {
                $data['arrow'] = '→';
                $data['dates'] = date('d.m.Y', strtotime($bullet['segment'][0]['flight'][0]['departure_date']));
                $data['footer'] = 'Туда: ' ;//.  $airlines[$bullet[0]['proposals'][0]['segment'][0]['operating_carrier']]['name'];
            }

            $terms = current($bullet['terms']);
            $data['price'] = $terms['price'];
            $data['updatedD'] = date('d.m.Y');

            $response[$key]['message'] = $this->makeRequestMessage($data, 'api_send_tickets');
            if ($url = (json_decode($this->travel_payouts_services->getURL($search_id, $terms['url']), true)['url'] ?? null)) {
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
