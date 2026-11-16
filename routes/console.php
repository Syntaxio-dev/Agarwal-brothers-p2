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
//
// IMPORTANT: use Schedule::call(), never Schedule::command(). Schedule::command() starts a
// separate PHP process with exec/proc_open, and Hostinger shared hosting disables those
// functions: the scheduler then reports "DONE" in 20 ms and silently does nothing (the mail
// queue never drained and the nightly backup never ran). A closure runs inside the same
// process, so it works there. Every closure needs ->name() so withoutOverlapping() can lock it.
Schedule::call(fn () => Artisan::call('queue:work', [
    '--stop-when-empty' => true,
    '--max-time' => 50,
    '--tries' => 3,
]))
    ->name('mail-queue')
    ->everyMinute()
    ->withoutOverlapping(5);

Schedule::call(fn () => Artisan::call('backup:run'))
    ->name('nightly-backup')
    ->dailyAt('02:30')
    ->withoutOverlapping();

Schedule::call(fn () => Artisan::call('housekeeping:run'))
    ->name('nightly-housekeeping')
    ->dailyAt('03:30')
    ->withoutOverlapping();
