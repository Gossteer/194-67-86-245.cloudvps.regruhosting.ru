<?php

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

Route::post('/get-tags', 'TagController@all');
Route::post('/save-tags', 'TagController@save');
Route::post('/remove-tags', 'TagController@remove');

Route::post('/get-requests', 'RequestController@all');
Route::post('/search-tickets', 'RequestController@searchTickets');
Route::post('/delete-request', 'RequestController@delete');
Route::get('/get-request/{request_id}', 'RequestController@get_data');

Route::post('/bot', 'VkGroupEventsListenerController@bot');

Route::get('/test', 'TestController@test');

Route::get('test', function () {
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
    $keyboard->buttons[0] = ['action' => [
        'type' => "open_link",
        'link' => '',
        'label' => '',
        'payload' => ''
    ]];


    $lol['keyboard'] = $keyboard;
    dd(json_encode($lol) );
});

Route::get('/price-calendar', 'RequestController@priceCalendar');
Route::get('/request-aviabot/{id}', 'RequestController@requestAviabot');
