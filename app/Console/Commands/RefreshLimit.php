<?php

namespace App\Console\Commands;

use App\Models\Request;
use App\Models\UserChat;
use Illuminate\Console\Command;

class RefreshLimit extends Command
{
    protected $signature = 'limit:refresh';
    protected $description = 'Refresh limit';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $user_chats = UserChat::with('user.requests')->get();

        foreach ($user_chats as $user_chat) {
            foreach ($user_chat->user->requests as $request) {
                $request->currentLimit = 0;
                $request->save();
            }
        }
    }
}
