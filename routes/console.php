<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Laravel scheduler minimum interval is 1 minute
// For sub-minute intervals, you need to keep the scheduler running with:
// php artisan schedule:work
// Or use a different approach like queued jobs
Schedule::command('rides:auto-reject')->everyMinute();
