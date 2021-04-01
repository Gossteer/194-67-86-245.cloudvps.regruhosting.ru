<?php

namespace App\Console\Commands;

use App\Services\UserCheapFlightsApiMessagesService;
use Illuminate\Console\Command;

class SendMessages extends Command
{
    protected $signature = 'messages:send';
    protected $description = 'Send messages';

    private $service;

    public function __construct()
    {
        parent::__construct();

        $this->service = new UserCheapFlightsApiMessagesService();
    }

    public function handle()
    {
        $this->service->sendApiMessagesToAllUsers();
    }
}
