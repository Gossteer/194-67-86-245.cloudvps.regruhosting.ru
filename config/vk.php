<?php

return [
    'api' => [
        'VK_API_ENDPOINT' => env('VK_API_ENDPOINT', 'https://api.vk.com/method/'),
        'VK_API_VERSION' => env('VK_API_VERSION', 5.103),
        'VK_GROUP_API_TOKEN' => env('VK_GROUP_API_TOKEN')
    ],
    'groups' => [
        'MIX_MAIN_VK_PUBLIC_ID' => env('MIX_MAIN_VK_PUBLIC_ID', 192548341),
        'SEND_FIRST_SEARCH_VK_PUBLIC_ID' => env('SEND_FIRST_SEARCH_VK_PUBLIC_ID', 204613902),
        'SEND_SUBSCRIPTION_SEARCH_VK_PUBLIC_ID' => env('SEND_SUBSCRIPTION_SEARCH_VK_PUBLIC_ID', 205982619),
        'HELLO_MESSAGE_VK_PUBLIC_ID' => env('HELLO_MESSAGE_VK_PUBLIC_ID', 205982527)
    ],
    'VK_APP_ID' => env('VK_APP_ID'),
];
