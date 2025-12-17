<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Auto-reject rides if driver doesn't respond within 30 seconds
        $schedule->command('rides:auto-reject')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->runInBackground();

        // Keep queue worker alive - check every minute
        $schedule->exec('pgrep -f "queue:work" || nohup php artisan queue:work --sleep=3 --tries=3 --max-time=3600 > storage/logs/queue.log 2>&1 &')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
