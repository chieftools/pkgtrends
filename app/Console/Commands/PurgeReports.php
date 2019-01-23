<?php

namespace IronGate\Pkgtrends\Console\Commands;

use Illuminate\Console\Command;
use IronGate\Pkgtrends\Models\Report;

class PurgeReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pkgtrends:purge-reports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge all unsubscribed reports.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $count = Report::query()->whereDoesntHave('subscribers')->delete();

        $this->info("Purged {$count} reports!");
    }
}
