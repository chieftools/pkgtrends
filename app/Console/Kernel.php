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

        $schedule->command('cache:prune-stale-tags')
                 ->hourly()
                 ->withoutOverlapping()->runInBackground()->onOneServer();
    }

    private function scheduleHex(Schedule $schedule): void
    {
        // Every day we import the daily download statistics
        $schedule->command(Commands\Import\Hex\Downloads::class)
                 ->dailyAt('04:00')
                 ->withoutOverlapping()->runInBackground()->onOneServer()
                 ->appendOutputTo($this->scheduleLogPath());

        // Every sunday we cleanup our internal package index / stats that are old or no longer in use
        $schedule->command(Commands\Import\Hex\Cleanup::class)
                 ->sundays()->at('03:00')
                 ->withoutOverlapping()->runInBackground()->onOneServer()
                 ->appendOutputTo($this->scheduleLogPath());
    }

    private function schedulePyPI(Schedule $schedule): void
    {
        // Every day we import the daily download statistics
        $schedule->command(Commands\Import\PyPI\Downloads::class)
                 ->dailyAt('04:15')
                 ->withoutOverlapping()->runInBackground()->onOneServer()
                 ->appendOutputTo($this->scheduleLogPath());

        // Every sunday we cleanup our internal package index / stats that are old or no longer in use
        $schedule->command(Commands\Import\PyPI\Cleanup::class)
                 ->sundays()->at('03:15')
                 ->withoutOverlapping()->runInBackground()->onOneServer()
                 ->appendOutputTo($this->scheduleLogPath());

        // Every saterday we update our internal package index with new and/or updated package descriptions
        $schedule->command(Commands\Import\PyPI\Packages::class)
                 ->saturdays()->at('06:00')
                 ->withoutOverlapping()->runInBackground()->onOneServer()
                 ->appendOutputTo($this->scheduleLogPath());
    }

    private function scheduleQueue(Schedule $schedule): void
    {
        // Dispatches a job calling a ping hook so we know the queue is active
        $schedule->command(Commands\QueueHealthCheck::class)
                 ->everyMinute()
                 ->withoutOverlapping()->runInBackground()->onOneServer()
                 ->appendOutputTo($this->scheduleLogPath());

        // See: https://laravel.com/docs/9.x/horizon#metrics
        $schedule->command(SnapshotCommand::class)
                 ->everyFiveMinutes()
                 ->withoutOverlapping()->runInBackground()->onOneServer()
                 ->appendOutputTo($this->scheduleLogPath());
    }

    private function scheduleSubscriptions(Schedule $schedule): void
    {
        // Every monday we send the weekly reports to our subscribers
        $schedule->command(Commands\SendWeeklyReports::class)
                 ->mondays()->at('08:00')
                 ->withoutOverlapping()->runInBackground()->onOneServer()
                 ->appendOutputTo($this->scheduleLogPath());

        // Every day purge the unconfirmed subscriptions
        $schedule->command(Commands\PurgeSubscriptions::class)
                 ->dailyAt('01:00')
                 ->withoutOverlapping()->runInBackground()->onOneServer()
                 ->appendOutputTo($this->scheduleLogPath());

        // Every day purge the unsubscribed reports
        $schedule->command(Commands\PurgeReports::class)
                 ->dailyAt('01:15')
                 ->withoutOverlapping()->runInBackground()->onOneServer()
                 ->appendOutputTo($this->scheduleLogPath());
    }

    private function scheduleLogPath(): string
    {
        return config('logging.scheduled_commands_file');
    }
}
