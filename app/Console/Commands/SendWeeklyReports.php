<?php

namespace ChiefTools\Pkgtrends\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use ChiefTools\Pkgtrends\Models\Report;
use ChiefTools\Pkgtrends\Mail\WeeklyReport;
use Illuminate\Database\Eloquent\Collection;
use ChiefTools\Pkgtrends\Models\Subscription;

class SendWeeklyReports extends Command
{
    protected $signature   = 'pkgtrends:weekly {--force}';
    protected $description = 'Send weekly trend reports to subscriptions.';

    public function handle(): void
    {
        Report::query()->whereHas('subscriptions', function ($query) {
            $query->confirmed();

            if (!$this->option('force')) {
                $query->notNotifiedInLastDays();
            }
        })->chunk(50, function (Collection $reports) {
            $reports->each(function (Report $report) {
                $trends = $report->getTrends();

                if ($trends->hasData()) {
                    $report->subscriptions->each(function (Subscription $subscription) use ($trends, $report) {
                        $this->info("Sending weekly report:{$report->id} to subscription:{$subscription->id}");

                        Mail::to($subscription)->send(
                            new WeeklyReport(
                                $trends->getFormattedTitle(),
                                $report->permalink,
                                $trends->getData(),
                                $subscription,
                            ),
                        );

                        $subscription->markNotified();
                    });
                }
            });
        });

        if (!empty(config('app.ping.weekly'))) {
            retry(3, static fn () => file_get_contents(config('app.ping.weekly')), 15);
        }
    }
}
