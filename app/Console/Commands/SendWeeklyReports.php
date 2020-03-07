<?php

namespace IronGate\Pkgtrends\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use IronGate\Pkgtrends\Models\Report;
use Illuminate\Database\Eloquent\Builder;
use IronGate\Pkgtrends\Mail\WeeklyReport;
use IronGate\Pkgtrends\Models\Subscription;
use Illuminate\Database\Eloquent\Collection;

class SendWeeklyReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pkgtrends:weekly {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send weekly trend reports to subscriptions.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Report::query()->whereHas('subscriptions', function (Builder $query) {
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
                                $subscription
                            )
                        );

                        $subscription->markNotified();
                    });
                }
            });
        });

        // If the range has been changed presume we are not running in the cron and therefore should not ping the healthcheck url
        if (!empty(config('app.ping.weekly'))) {
            retry(3, function () {
                file_get_contents(config('app.ping.weekly'));
            }, 15);
        }
    }
}
