<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('events:send-daily')
            ->dailyAt((string) config('events.notify_at', '09:00'))
            ->timezone((string) config('events.timezone', 'UTC'))
            ->when(fn () => (bool) config('events.enabled', true))
            ->withoutOverlapping(10)
            ->onOneServer()
            ->runInBackground();

        $coffeeTimezone = (string) config('coffee.timezone', 'UTC');
        $coffeeBotConfigured = fn () => (string) config('coffee.bot_token', '') !== '';

        // The command itself checks coffee_settings (enabled, matching day,
        // frequency) in --auto mode, so it is safe to trigger daily.
        $schedule->command('coffee:generate-meetings --auto')
            ->dailyAt('09:00')
            ->timezone($coffeeTimezone)
            ->withoutOverlapping(10)
            ->onOneServer()
            ->runInBackground();

        $schedule->command('coffee:send-confirmations')
            ->dailyAt('10:00')
            ->timezone($coffeeTimezone)
            ->withoutOverlapping(10)
            ->onOneServer()
            ->runInBackground();

        $schedule->command('coffee:telegram-poll')
            ->everyMinute()
            ->when($coffeeBotConfigured)
            ->withoutOverlapping(5)
            ->onOneServer()
            ->runInBackground();
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
