<?php

namespace App\Console\Commands;

use App\Services\UserCheapFlightsApiMessagesService;
use Illuminate\Console\Command;

class SendMessages extends Command
{
    protected $signature = 'messages:send';
    protected $description = 'Send messages';

    private UserCheapFlightsApiMessagesService $service;

    public function __construct(UserCheapFlightsApiMessagesService $user_cheap_flightsApi_messagesService)
    {
        parent::__construct();

        $this->service = $user_cheap_flightsApi_messagesService;
    }

    public function handle()
    {
        $this->service->sendApiMessagesToAllUsers();
    }
}
