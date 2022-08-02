<?php

namespace ChiefTools\Pkgtrends\Console\Commands;

use Illuminate\Console\Command;
use ChiefTools\Pkgtrends\Models\Report;

class PurgeReports extends Command
{
    protected $signature   = 'pkgtrends:purge-reports';
    protected $description = 'Purge all unsubscribed reports.';

    public function handle(): void
    {
        $count = Report::query()->whereDoesntHave('subscriptions')->delete();

        $this->info("Purged {$count} reports!");

        if (!empty(config('app.ping.purge_reports'))) {
            retry(3, static fn () => file_get_contents(config('app.ping.purge_reports')), 15);
        }
    }
}
