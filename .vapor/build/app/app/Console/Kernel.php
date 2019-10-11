<?php

namespace IronGate\Pkgtrends\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\PurgeReports::class,
        Commands\SendWeeklyReports::class,
        Commands\PurgeSubscriptions::class,

        Commands\Import\Hex\Cleanup::class,
        Commands\Import\Hex\Downloads::class,

        Commands\Import\PyPI\Cleanup::class,
        Commands\Import\PyPI\Packages::class,
        Commands\Import\PyPI\Downloads::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        if (!config('app.cron')) {
            return;
        }

        $this->scheduleHex($schedule);
        $this->schedulePyPI($schedule);
        $this->scheduleSubscriptions($schedule);
    }

    private function scheduleHex(Schedule $schedule): void
    {
        // Every day we import the daily download statistics
        $schedule->command(Commands\Import\Hex\Downloads::class)
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->dailyAt('04:00');

        // Every sunday we cleanup our internal package index / stats that are old or no longer in use
        $schedule->command(Commands\Import\Hex\Cleanup::class)
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->at('03:00')
                 ->sundays();
    }

    private function schedulePyPI(Schedule $schedule): void
    {
        // Every day we import the daily download statistics
        $schedule->command(Commands\Import\PyPI\Downloads::class)
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->dailyAt('04:15');

        // Every sunday we cleanup our internal package index / stats that are old or no longer in use
        $schedule->command(Commands\Import\PyPI\Cleanup::class)
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->at('03:15')
                 ->sundays();

        // Every saterday we update our internal package index with new and/or updated package descriptions
        $schedule->command(Commands\Import\PyPI\Packages::class)
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->at('06:00')
                 ->saturdays();
    }

    private function scheduleSubscriptions(Schedule $schedule): void
    {
        // Every monday we send the weekly reports to our subscribers
        $schedule->command(Commands\SendWeeklyReports::class)
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->at('08:00')
                 ->mondays();

        // Every day purge the unconfirmed subscriptions
        $schedule->command(Commands\PurgeSubscriptions::class)
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->dailyAt('01:00');

        // Every day purge the unsubscribed reports
        $schedule->command(Commands\PurgeReports::class)
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->dailyAt('01:15');
    }
}
