<?php

namespace App\Console;

use App\Jobs\PayrunJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
 
        $schedule->command('files:delete')->everyMinute();

        $schedule->command('reminder:send')->everyMinute();

        $schedule->command('salary_reminder:send')->everyMinute();

        $schedule->command('payrun:run')->everyMinute();


        $schedule->command('holiday:renew')->everyMinute();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
