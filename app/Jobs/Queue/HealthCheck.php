<?php

namespace ChiefTools\Pkgtrends\Jobs\Queue;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class HealthCheck implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public function handle(): void
    {
        $client = http(timeout: 3);

        try {
            retry(3, static fn () => $client->get(config('app.ping.queue')), 100);
        } catch (Exception) {
            // We try again later if we cannot reach the monitor!
        }
    }
}
