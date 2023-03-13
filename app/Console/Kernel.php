<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\AutoDeleteFileVerifyIdentity::class,
        Commands\ChargePayment::class,
    ];
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // schedule delete files verification
        $schedule->command('verifyIdentity:file')->dailyAt('09:00');

        // schedule charge payment
        $schedule->command('payment:stripe')->dailyAt('00:00');
        // $arr_hours = ['11:00', '11:05', '12:15', '12:30', '13:00', '13:05', '14:00', '14:05', '15:00', '15:05', '16:15', '16:30', '18:00', '18:05'];
        // foreach ($arr_hours as $time) {
        //     $schedule->command('payment:stripe')->dailyAt($time);
        // }

        // schedule service recommend
        $schedule->command('service:recommend')->everyFiveMinutes();

        // sechedule tag recommend
        $schedule->command('hashtag:recommend')->weeklyOn(1, '8:00');

        // $schedule->command('test:cron')->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
