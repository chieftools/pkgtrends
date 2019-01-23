<?php

namespace IronGate\Pkgtrends\Console\Commands;

use Illuminate\Console\Command;
use IronGate\Pkgtrends\Models\Subscriber;

class PurgeSubscribers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pkgtrends:purge-subscribers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge all unconfirmed subscribers after 48 hours.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $count = Subscriber::query()->hasNotConfirmedInHours()->delete();

        $this->info("Purged {$count} subscribers!");
    }
}
