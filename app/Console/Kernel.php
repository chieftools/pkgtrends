<?php

namespace ChiefTools\Pkgtrends\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Horizon\Console\SnapshotCommand;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }

    protected function schedule(Schedule $schedule): void
    {
        if (!config('app.cron')) {
            return;
        }

        $this->scheduleHex($schedule);
        $this->schedulePyPI($schedule);
        $this->scheduleQueue($schedule);
        $this->scheduleSubscriptions($schedule);

        $schedule->command('cache:prune-stale-tags')->hourly();
    }

    private function scheduleHex(Schedule $schedule): void
    {
        // Every day we import the daily download statistics
        $schedule->command(Commands\Import\Hex\Downloads::class)
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onOneServer()
                 ->dailyAt('04:00');

        // Every sunday we cleanup our internal package index / stats that are old or no longer in use
        $schedule->command(Commands\Import\Hex\Cleanup::class)
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onOneServer()
                 ->at('03:00')
                 ->sundays();
    }

    private function schedulePyPI(Schedule $schedule): void
    {
        // Every day we import the daily download statistics
        $schedule->command(Commands\Import\PyPI\Downloads::class)
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onOneServer()
                 ->dailyAt('04:15');

        // Every sunday we cleanup our internal package index / stats that are old or no longer in use
        $schedule->command(Commands\Import\PyPI\Cleanup::class)
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onOneServer()
                 ->at('03:15')
                 ->sundays();

        // Every saterday we update our internal package index with new and/or updated package descriptions
        $schedule->command(Commands\Import\PyPI\Packages::class)
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->at('06:00')
                 ->saturdays();
    }

    private function scheduleQueue(Schedule $schedule): void
    {
        // Dispatches a job calling a ping hook so we know the queue is active
        $schedule->command(Commands\QueueHealthCheck::class)
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onOneServer()
                 ->everyMinute();

        // See: https://laravel.com/docs/9.x/horizon#metrics
        $schedule->command(SnapshotCommand::class)
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onOneServer()
                 ->everyFiveMinutes();
    }

    private function scheduleSubscriptions(Schedule $schedule): void
    {
        // Every monday we send the weekly reports to our subscribers
        $schedule->command(Commands\SendWeeklyReports::class)
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onOneServer()
                 ->at('08:00')
                 ->mondays();

        // Every day purge the unconfirmed subscriptions
        $schedule->command(Commands\PurgeSubscriptions::class)
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onOneServer()
                 ->dailyAt('01:00');

        // Every day purge the unsubscribed reports
        $schedule->command(Commands\PurgeReports::class)
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onOneServer()
                 ->dailyAt('01:15');
    }
}
