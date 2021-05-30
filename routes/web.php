<?php

use App\Services\VkApi;
use Illuminate\Support\Facades\Route;


//Методы по работе с подписками и билетами
Route::get('/price-calendar', 'RequestController@priceCalendar'); //Календарь цен
Route::get('/request-aviabot/{id}', 'RequestController@requestAviabot'); //Непомню для чего
Route::post('/get-url', 'RequestController@getURL'); //Получение ссылки на билет (работает в связке с поиском в реальном времени)
Route::post('/search-tickets', 'RequestController@searchTickets'); //Поиск билетов в 'реальном времени'
Route::post('/send-first-search-tickets', 'RequestController@sendFirstSearchTickets'); // Отправка n-первых билетов в личные сообщения пользователя
Route::post('/delete-request', 'RequestController@delete'); //Удаление подписки
Route::post('/get-cheapest-ticket', 'IndexController@getCheapestTicket'); //Получение самых дешёвых билетов
Route::post('/save-chat', 'ChatController@save'); //Создание чата (хз что это)
Route::get('/chats', 'ChatController@list'); //Получение всех id чатов
Route::post('/save-user-api-request', 'UserController@saveRequest'); //Создание подписки и в случае если пользователя не существует, то и его тоже
Route::post('/get-requests', 'RequestController@all'); //Получаем все подписки пользователя
Route::get('/get-request/{request_id}', 'RequestController@get_data'); //Получаем подписку

//Методы для работы с пользователем
Route::post('/user/{user}/data/{key}', 'UserDataController@store'); //Создание пользоваетля и его data(и её обновление)
Route::get('/user/{user}/data', 'UserDataController@get'); //Получение ключа и value data пользователя

//Методы для работы с группами/чатами
Route::get('/groups', 'GroupController@list'); //Получение списка групп(ботов) по полу
Route::get('/group_allowed/{user_id}/{group_id}', 'GroupController@group_allowed'); //Проверяем есть ли доступ у группы для пользователя
Route::post('/save-groups', 'GroupController@save'); //Создание/получение группы/пользователя и соединение данных
Route::post('/check-group-enable', 'GroupController@checkGroupEnable'); //Проверяет, существует ли у пользователя данный чат

//Методы для работы с тегами
Route::post('/get-tags', 'TagController@all'); //Получаем все теги для данного пользователя
Route::post('/save-tags', 'TagController@save'); //Сохраняем/перезаписываем теги пользователя
Route::post('/remove-tags', 'TagController@remove'); //Отсоединяем тег пользователя

//Тестовые методы
Route::get('/test', 'TestController@test');
Route::get('testbutton', function () {
    $keyboard = new stdClass();
    $keyboard->one_time = false;
    $keyboard->inline = true;
    $buttons = new stdClass();
    $buttons->action = [
        'type' => "open_link",
        'link' => '',
        'label' => '',
        'payload' => ''
    ];
    $keyboard->buttons[0] = [$buttons];


    $lol['keyboard'] = [
        'one_time' => true,
        'inline' => true,
        'buttons' => [
            [
                [
                    'action' => [
                        'type' => "open_link",
                        'link' => '',
                        'label' => '',
                        'payload' => ''
                    ]
                ]

            ]
        ]
    ];

    $keyboard = [
        'one_time' => false,
        'inline' => true,
        "buttons" => [[
            [
                "action" => [
                    'type' => "open_link",
                    'link' => '',
                    "payload" => '{"button": "1"}',
                    "label" => "Фрукты?"
                ],
                "color" => "default"
            ],
        ]]
    ];

    // $keyboard;
    dd(json_encode($keyboard, JSON_UNESCAPED_UNICODE), $keyboard);
});
Route::get('users/send/{user_id}', function ($user_id) {
    //382960669
    $senSirvice = new VkApi();
    $keyboard = [
        'one_time' => false,
        'inline' => true,
        "buttons" => [[
            [
                "action" => [
                    'type' => "open_link",
                    'link' => 'https://github.com/Gossteer/194-67-86-245.cloudvps.regruhosting.ru',
                    "label" => "Фрукты?"
                ]
            ],
        ]]
    ];
    dd($senSirvice->messagesSend(['user_id' => $user_id], 'Семён приветик!1! Напиши пожалуйста мне в тг, если это видишь', '204613902', true, $keyboard));

    // $returnValue = json_decode('{"error":{"error_code":912,"error_msg":"This is a chat bot feature, change this status in settings","request_params":[{"key":"method","value":"messages.send"},{"key":"oauth","value":"1"},{"key":"user_id","value":"382960669"},{"key":"group_id","value":"198385755"},{"key":"random_id","value":"11708684"},{"key":"domain","value":"App"},{"key":"title","value":"Дешевые авиабилеты"},{"key":"dont_parse_links","value":"0"},{"key":"keyboard","value":"{\\"one_time\\":false,\\"inline\\":true,\\"buttons\\":[[{\\"action\\":{\\"type\\":\\"open_link\\",\\"link\\":\\"https:\\\\\\/\\\\\\/www.aviasales.ru\\\\\\/search?origin_iata=CHI&destination_iata=MIA&depart_date=2021-05-18&with_request=1&adults=1&children=0&infants=0&trip_class=0&marker=122890.app¤cy=RUB&oneway=1\\",\\"label\\":\\"Проверить цену\\"}}]]}"},{"key":"attachment","value":"https:\\/\\/www.aviasales.ru\\/"},{"key":"v","value":"5.103"}]}}', true);

    // $lol = [
    //     3123123 => '4444',
    //     3144123 => '4444',
    //     3123123 => '4444',
    //     3144123 => '1234',
    //     3123123 => '555',
    // ];


    // dd($returnValue['error']['request_params'][3]['value'], $returnValue['error']['error_code'], array_unique($lol), array_filter($lol, function ($key, $value) use($lol)
    // {
    //     if (in_array($value, $lol)) {
    //         # code...
    //     } else {
    //         # code...
    //     }

    //     return in_array($value, $lol) ? false : true;
    // }, ARRAY_FILTER_USE_KEY));
    // return $senSirvice;

    // dd((bool)'');
    // $text = "";
    // foreach ($lol as $key => $value) {
    //     $text .=  "Включите пожалуйста возможности ботов в группе: https://vk.com/public" . $value . "\n";
    // }

    // return $text;
});

//Непонятные маршруты
Route::post('/allow-messages', 'IndexController@allowMessages');
Route::get('/', 'IndexController@index');
Route::post('/setup-confirmation', 'IndexController@setupConfirmation');
Route::post('/save-admin', 'AdminController@save'); //Вроде бы для редактирование сообщений
Route::post('/bot', 'VkGroupEventsListenerController@bot');
