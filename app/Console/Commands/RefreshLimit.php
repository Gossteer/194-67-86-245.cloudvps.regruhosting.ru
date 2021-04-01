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
        $users = UserChat::all();

        foreach ($users as $user) {
            $requests = Request::where(['user_id' => $user->user_id])->get();

            foreach ($requests as $request) {
                $request->currentLimit = 0;
                $request->save();
            }
        }
    }
}
