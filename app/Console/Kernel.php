<?php

namespace IronGate\Pkgtrends\Console;

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
        Commands\Import\PyPI\Packages::class,
        Commands\Import\PyPI\Downloads::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
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
    }
}
