<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;


//Методы по работе с подписками и билетами
Route::get('/price-calendar', 'RequestController@priceCalendar'); //Календарь цен
Route::get('/request-aviabot/{id}', 'RequestController@requestAviabot'); //Непомню для чего
Route::post('/get-url', 'RequestController@getURL'); //Получение ссылки на билет (работает в связке с поиском в реальном времени)
Route::post('/search-tickets', 'RequestController@searchTickets'); //Поиск билетов в 'реальном времени'
Route::get('/search-id-tickets', 'RequestController@searchResult'); //Поиск билетов в 'реальном времени' по search_id
Route::post('/send-first-search-tickets', 'RequestController@sendFirstSearchTickets'); // Отправка n-первых билетов в личные сообщения пользователя
Route::post('/delete-request', 'RequestController@delete'); //Удаление подписки
Route::post('/subscription-create', 'SubscriptionController@createSupbscription'); //Создание кастомной подписки
Route::get('/get-subscriptions-byuser', 'SubscriptionController@getSubscriptionsByUser'); //Создание получение кастомной подписки
Route::post('/delete-subscription/{subscription_id}', 'SubscriptionController@deleteSubscription');

// Route::options('test-marusa', function (Request $request) {
//     header("Access-Control-Allow-Headers: Content-Type, Accept");
//     header("Access-Control-Allow-Origin: https://skill-debugger.marusia.mail.ru");
//     Log::info("1", $request->all());

//     return 1;
// });

// Route::post('test-marusa', function (Request $request) {
//     header("Access-Control-Allow-Origin: *");
//     Log::info("2", [json_encode(['command' => 124325])]);

//     return response()->json(json_decode('{
//         "response": {
//         "text": "Сейчас очередь в столовой 5 человек.",
//         "tts": "Сейчас очередь в столовой пять человек.",
//         "buttons": [
//         {
//         "title": "Надпись на кнопке",
//         "payload": '. json_encode(['command' => 124325]) .',
//         "url": "https://vk.com/gossteer"
//         }
//         ],
//         "end_session": false
//         },
//         "session": {
//         "session_id": "' . $request['session']['session_id'] . '",
//         "user_id": "' . $request['session']['user_id'] . '",
//         "new": false,
//         "message_id": ' . $request['session']['message_id'] . '
//         },
//         "version": "1.0"
//         }', true), 200, ["Access-Control-Allow-Headers: Content-Type, Accept",
//         "Access-Control-Allow-Origin: https://skill-debugger.marusia.mail.ru"]);
// });

//Методы для работы с ботом(и)
Route::post('/vk-bot-api-v2', 'VkBotController@responseToMessage');

//Методы для работы с навыком Маруси
Route::post('/vk-marusa-hook', 'VkBotController@responseToMessage');

//Методы по работе со статистикой
Route::get('/get-static-for-user', 'UserDataController@staticDataForUserStartMeny'); // Статистика юзера и о юзере
Route::get('/get-full-static-for-first-install', 'StaticController@getFullStaticForFirstInstall'); // общая статистика

Route::post('/save-chat', 'ChatController@save'); //Создание чата (хз что это)
Route::get('/chats', 'ChatController@list'); //Получение всех id чатов
Route::post('/save-user-api-request', 'UserController@saveRequest'); //Создание подписки и в случае если пользователя не существует, то и его тоже
Route::post('/get-requests', 'RequestController@all'); //Получаем все подписки пользователя
Route::get('/get-request/{request_id}', 'RequestController@get_data'); //Получаем подписку

//Методы для работы с пользователем
Route::post('/user-add-favorite-ficket', 'UserDataController@addFavoriteTicket'); //Создание/добавления списка избранных билетов
Route::post('/user-delete-favorite-ficket', 'UserDataController@deleteFavoriteTicket'); //Удаление избранного билета
Route::get('/user-get-favorite-ficket/{user_id}', 'UserDataController@getFavoriteTickets'); //Получение избранных билетов
Route::post('/user/{user}/data/{key}', 'UserDataController@store'); //Создание пользоваетля и его data(и её обновление)
Route::get('/user/{user}/data', 'UserDataController@get'); //Получение ключа и value data пользователя

//Методы для работы с группами/чатами
Route::get('/group_allowed/{user_id}/{group_id}', 'ChatController@chat_allowed'); //Проверяем есть ли доступ у группы для пользователя
Route::post('/check-group-enable', 'ChatController@checkGroupEnable'); //Проверяет, существует ли у пользователя данный чат

//Методы для работы с тегами
Route::post('/get-tags', 'TagController@all'); //Получаем все теги для данного пользователя
Route::post('/save-tags', 'TagController@save'); //Сохраняем/перезаписываем теги пользователя
Route::post('/remove-tags', 'TagController@remove'); //Отсоединяем тег пользователя

//Тестовые методы
Route::get('/test', 'TestController@test');

//Административные методы
Route::post('/update-message', 'AdminController@messageUpdate');
Route::get('/get-messages', 'AdminController@getMessages');

//Более или пока не используются
Route::post('/get-cheapest-ticket', 'IndexController@getCheapestTicket'); //Получение самых дешёвых билетов
Route::post('/allow-messages', 'IndexController@allowMessages');
Route::get('/', 'IndexController@index');
Route::post('/setup-confirmation', 'IndexController@setupConfirmation');
Route::post('/save-admin', 'AdminController@save'); //Вроде бы для редактирование сообщений
Route::post('/bot', 'VkGroupEventsListenerController@bot');
Route::get('/groups', 'GroupController@list'); //Получение списка групп(ботов) по полу
Route::post('/save-groups', 'GroupController@save'); //Создание/получение группы/пользователя и соединение данных
