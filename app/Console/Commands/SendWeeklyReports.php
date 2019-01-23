<?php

namespace IronGate\Pkgtrends\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use IronGate\Pkgtrends\Models\Report;
use IronGate\Pkgtrends\Mail\WeeklyReport;
use IronGate\Pkgtrends\Models\Subscriber;
use Illuminate\Database\Eloquent\Collection;

class SendWeeklyReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pkgtrends:weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send weekly trend reports to subscribers.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Report::query()->whereHas('subscribers', function ($query) {
            $query->confirmed()->notNotifiedInLastDays();
        })->chunk(50, function (Collection $reports) {
            $reports->each(function (Report $report) {
                $trends = $report->getTrends();

                if ($trends->hasData()) {
                    $report->subscribers->each(function (Subscriber $subscriber) use ($trends, $report) {
                        $this->info("Sending weekly report:{$report->id} to subscriber:{$subscriber->id}");

                        Mail::to($subscriber)->send(
                            new WeeklyReport($trends->getFormattedTitle(), $trends->getTrendsData(), $subscriber)
                        );

                        $subscriber->wasNotified();
                    });
                }
            });
        });
    }
}
