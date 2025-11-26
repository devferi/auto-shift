<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('attendance:generate-schedules')->dailyAt('00:05');
        $schedule->command('attendance:generate-jobs')->dailyAt('00:10');
        $schedule->command('attendance:run-jobs')->everyMinute();
    }
}

