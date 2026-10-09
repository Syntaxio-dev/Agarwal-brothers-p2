<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// One cron entry ("* * * * * php artisan schedule:run") is enough: this drains the
// mail queue every minute. With Supervisor/systemd run `queue:work` instead and
// this stays harmless (withoutOverlapping).
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3')
    ->everyMinute()
    ->withoutOverlapping(5);
Schedule::command('backup:run')->dailyAt('02:30')->withoutOverlapping();
