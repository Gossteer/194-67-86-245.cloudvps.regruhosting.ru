<?php

namespace App\Services;

use App\Models\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserCheapFlightsApiMessagesService
{
    /**
     * Сервис отправки сообщений
     *
     * @var VkApi
     */
    private VkApi $api;
    /**
     * Сервис формирования сообщений для отправки
     *
     * @var FormationMessageServices
     */
    private FormationMessageServices $formation_message_services;

    public function __construct(VkApi $vk_api, FormationMessageServices $formation_message_services)
    {
        $this->api = $vk_api;
        $this->formation_message_services = $formation_message_services;
    }

    /**
     * Перебор всех юзеров и отправка им билетов по их подпискам
     *
     * @return void
     */
    public function sendApiMessagesToAllUsers()
    {
        $users = User::all();
        foreach ($users as $user) {
            $this->sendUserAllMessages($user);
        }
    }

    /**
     * Отправка пользователю найденных для него билетов по подписке
     *
     * @param User $user
     * @return void
     */
    public function sendUserAllMessages($user)
    {
        $errors = [];
        foreach (Request::getAllByUserId($user->id) as $request) {
            Log::info('Search flights for user_id:' . $user->id);
            foreach ($request->getFlightsFromApi() as $flight) {
                // \Log::info('Search flight '.$user->hasRequestReceived($flight['id']));
                if (!$user->hasRequestReceived($flight['id']) /* && $request->inRequestAllowableUpdatedDatesRadius($flight)*/) {
                    Log::info('has flights for user_id:' . $user->id);

                    $response[] = $this->api->messagesSend(
                        ['user_id' => $user->id],
                        $request->makeRequestMessage($flight),
                        $request->group_id ?? config('vk.groups.MIX_MAIN_VK_PUBLIC_ID'),
                        true,
                        $this->formation_message_services->makeRequestKeyboard(false, true, [
                            [
                                'link' => $request->getUrl($flight),
                                'label' => 'Проверить цену',
                                'type' => 'open_link'
                            ]
                        ]),
                    );

                    if (isset($response['error'])) {
                        $errors[$response['error']['error_code']] = $response['error']['request_params'][3]['value'];
                    }

                    $user->receivedRequest($flight['id'], null, $request->id);
                    sleep(1);
                }
            }
        }
        $this->sendError($errors);
    }

    /**
     * Обработка ошибок и отправка их администратору
     *
     * @param array $errors
     * @return void
     */
    public function sendError(array $errors = [])
    {
        $error_912 = [];
        foreach ($errors as $key => $value) {
            if (isset($value['error_code']) and isset($value['request_params'][3]['value']) and $value['error_code'] == 912) {
                $error_912[$key] = $value;
            }
        }

        if ($error_912) {
            $text = '';
            foreach ($error_912 as $key => $value) {
                $text .=  "Включите пожалуйста возможности ботов в группе: https://vk.com/public" . $value . "\n";
            }

            if ($text) {
                $this->api->messagesSend(
                    ['user_id' => '382960669'],
                    $text,
                    config('vk.groups.MIX_MAIN_VK_PUBLIC_ID'),
                    true
                );
            }
        }
    }

    /**
     * Формирование ссылки поиска на авиасеилс
     *
     * @param string $src_code
     * @param string $dst_code
     * @param string $departure_date
     * @param array $passenger
     * @param string $marker = '122890.miniарр_subscr'
     * @param string|null $return_date
     * @return string
     */
    public static function getUrlAviasales(string $src_code, string $dst_code, string $departure_date, array $passengers, string $trip_class, ?string $return_date = null, string $marker = "122890.miniapp_subscr"): string
    {
        return "https://www.aviasales.ru/search?origin_iata=$src_code&destination_iata=$dst_code&depart_date=$departure_date&with_request=1&adults={$passengers['adults']}&children={$passengers['children']}&infants={$passengers['infants']}&trip_class=$trip_class&marker=$marker&oneway=0" . ($return_date ? "&return_date=$return_date" : "");
    }

    // private function getErrorForGroup(array $errors = []): array
    // {
    //     return array_filter($errors, function ($key, $value) use(&$errors)
    //     {
    //         if (in_array($value, $errors)) {
    //             unset($errors[$key]);
    //         }
    //     });
    // }
}
