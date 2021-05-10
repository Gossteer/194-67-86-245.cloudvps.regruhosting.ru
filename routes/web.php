<?php

use App\Services\VkApi;

Route::get('/', 'IndexController@index');
Route::post('/allow-messages', 'IndexController@allowMessages');
Route::post('/setup-confirmation', 'IndexController@setupConfirmation');
Route::post('/get-cheapest-ticket', 'IndexController@getCheapestTicket');

Route::post('/save-admin', 'AdminController@save');
Route::post('/save-chat', 'ChatController@save');
Route::get('/chats', 'ChatController@list');
Route::post('/save-user-api-request', 'UserController@saveRequest');
Route::post('/user/{user}/data/{key}', 'UserDataController@store');
Route::get('/user/{user}/data', 'UserDataController@get');
Route::get('/groups', 'GroupController@list');
Route::get('/group_allowed/{user_id}/{group_id}', 'GroupController@group_allowed');

Route::post('/save-groups', 'GroupController@save');
Route::post('/check-group-enable', 'GroupController@checkGroupEnable');

Route::get('users/send/{user_id}', function ($user_id) {
    $senSirvice = new VkApi();
    $keyboard = [
        'one_time' => false,
        'inline' => true,
        "buttons" => [[
            [
                "action" => [
                    'type' => "open_link",
                    'link' => 'https://github.com/Gossteer/194-67-86-245.cloudvps.regruhosting.ru',
                    // "payload" => '{"button": "1"}',
                    "label" => "Фрукты?"
                ],
                "color" => "default"
            ],
        ]]
    ];
    $senSirvice->messagesSend(['user_id' => $user_id], 'Семён приветик!1! Напиши пожалуйста мне в тг, если это видишь', "192548341", true, $keyboard);
    dd($senSirvice);
    return $senSirvice;
});

Route::post('/get-tags', 'TagController@all');
Route::post('/save-tags', 'TagController@save');
Route::post('/remove-tags', 'TagController@remove');

Route::post('/get-requests', 'RequestController@all');
Route::post('/search-tickets', 'RequestController@searchTickets');
Route::post('/delete-request', 'RequestController@delete');
Route::get('/get-request/{request_id}', 'RequestController@get_data');

Route::post('/bot', 'VkGroupEventsListenerController@bot');

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

Route::get('/price-calendar', 'RequestController@priceCalendar');
Route::get('/request-aviabot/{id}', 'RequestController@requestAviabot');
Route::post('/get-url', 'RequestController@getURL');
