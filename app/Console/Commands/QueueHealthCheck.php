<?php

namespace IronGate\Pkgtrends\Console\Commands;

use Illuminate\Console\Command;
use IronGate\Pkgtrends\Jobs\Queue\HealthCheck;

class QueueHealthCheck extends Command
{
    protected $hidden = true;

    protected $signature   = 'pkgtrends:queue:health-check';
    protected $description = 'Dispatch a queue job to validate it\'s processing by pinging a health check URL.';

    public function handle(): int
    {
        if (empty(config('app.ping.queue'))) {
            $this->warn('Queue not being monitored because monitor webhook is not set.');

            return self::FAILURE;
        }

        dispatch(new HealthCheck);

        $this->info('Fired job onto the queue... standby for transmission!');

        return self::SUCCESS;
    }
}
