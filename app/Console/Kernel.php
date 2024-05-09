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
        $schedule->command('app:store-daily-checklist')->daily('8:00');
        $schedule->command('app:set-schedule-status')->hourly();
        $schedule->command('app:risk-reminder-email')->daily('8:00');
        $schedule->command('app:send-cqc-visit-reminder-emails')->daily('8:00');
        $schedule->command('app:send-cqc-visit-periodic-reminder')->daily('8:00');
        $schedule->command('telescope:prune --hours=48')->days([1, 3, 5]);
        $schedule->command('app:task-reminder-command')->daily('8:00');
        
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
