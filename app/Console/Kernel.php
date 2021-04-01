<?php

namespace App\Console;

use App\Console\Commands\RefreshLimit;
use App\Console\Commands\SendMessages;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command(SendMessages::class)->withoutOverlapping()->everyMinute();

        $schedule->command(RefreshLimit::class)->daily();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
    }
}
