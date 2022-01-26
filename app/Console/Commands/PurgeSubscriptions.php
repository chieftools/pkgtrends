<?php

namespace IronGate\Pkgtrends\Console\Commands;

use Illuminate\Console\Command;
use IronGate\Pkgtrends\Models\Subscription;

class PurgeSubscriptions extends Command
{
    protected $signature   = 'pkgtrends:purge-subscriptions';
    protected $description = 'Purge all unconfirmed subscriptions after 48 hours.';

    public function handle(): void
    {
        $count = Subscription::query()->hasNotConfirmedInHours()->delete();

        $this->info("Purged {$count} subscriptions!");

        if (!empty(config('app.ping.purge_subscriptions'))) {
            retry(3, static fn () => file_get_contents(config('app.ping.purge_subscriptions')), 15);
        }
    }
}
