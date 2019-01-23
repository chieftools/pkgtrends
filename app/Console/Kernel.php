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

        Commands\Import\PyPI\Cleanup::class,
        Commands\Import\PyPI\Packages::class,
        Commands\Import\PyPI\Downloads::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $this->schedulePyPI($schedule);
        $this->scheduleSubscriptions($schedule);
    }

    private function schedulePyPI(Schedule $schedule): void
    {
        // Every day at 04:00 we import the daily download statistics for PyPI
        $schedule->command(Commands\Import\PyPI\Downloads::class)
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->dailyAt('04:00');

        // Every saterday at 06:00 we update our internal package index with new and/or updated package descriptions
        $schedule->command(Commands\Import\PyPI\Packages::class)
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->at('06:00')
                 ->saturdays();

        // Every sunday at 03:00 we cleanup our internal package index / stats that are old or no longer in use
        $schedule->command(Commands\Import\PyPI\Cleanup::class)
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->at('03:00')
                 ->sundays();
    }

    private function scheduleSubscriptions(Schedule $schedule): void
    {
        // Every monday at 08:00 we send the weekly reports to our subscribers
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
                 ->dailyAt('02:00');
    }
}
