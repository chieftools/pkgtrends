<?php

namespace ChiefTools\Pkgtrends\Jobs\Subscriptions;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use ChiefTools\Pkgtrends\Models\Report;
use Illuminate\Queue\InteractsWithQueue;
use ChiefTools\Pkgtrends\Mail\WeeklyReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use ChiefTools\Pkgtrends\Models\Subscription;

class SendWeeklyReport implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public function __construct(
        private string $reportId,
        private bool $force = false,
    ) {}

    public function handle(): void
    {
        $report = Report::query()->find($this->reportId);

        if ($report === null) {
            return;
        }

        $subscriptions = $report->subscriptions()->confirmed();

        if (!$this->force) {
            $subscriptions->notNotifiedInLastDays();
        }

        if (!$subscriptions->exists()) {
            return;
        }

        $trends = $report->getTrends();

        if (!$trends->hasData()) {
            return;
        }

        $title        = $trends->getFormattedTitle();
        $dependencies = $trends->getData();

        $subscriptions->eachById(function (Subscription $subscription) use ($report, $title, $dependencies) {
            $subscription->setRelation('report', $report);

            Mail::to($subscription)->sendNow(
                new WeeklyReport(
                    $title,
                    $report->permalink,
                    $dependencies,
                    $subscription,
                ),
            );

            $subscription->markNotified();
        }, 100);
    }
}
