<?php

namespace ChiefTools\Pkgtrends\Console\Commands;

use Illuminate\Console\Command;
use ChiefTools\Pkgtrends\Models\Subscription;
use ChiefTools\Pkgtrends\Jobs\Subscriptions\SendWeeklyReport;

class SendWeeklyReports extends Command
{
    protected $signature   = 'pkgtrends:weekly {--force}';
    protected $description = 'Send weekly trend reports to subscriptions.';

    public function handle(): void
    {
        $force  = (bool)$this->option('force');
        $queued = 0;

        $subscriptions = Subscription::query()->confirmed();

        if (!$force) {
            $subscriptions->notNotifiedInLastDays();
        }

        $subscriptions
            ->select('report_id')
            ->distinct()
            ->eachById(function (Subscription $subscription) use ($force, &$queued) {
                dispatch(new SendWeeklyReport($subscription->report_id, $force));

                $queued++;
            }, 100, 'report_id', 'report_id');

        $this->info("Queued {$queued} weekly report jobs.");

        if (!empty(config('app.ping.weekly'))) {
            retry(3, static fn () => file_get_contents(config('app.ping.weekly')), 15);
        }
    }
}
