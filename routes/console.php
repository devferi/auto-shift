<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('attendance:generate-schedules')->dailyAt('00:05');
Schedule::command('attendance:generate-jobs')->dailyAt('00:10');
Schedule::command('attendance:run-jobs')->everyMinute();
