<?php

namespace IronGate\Pkgtrends\Console\Commands;

use Illuminate\Console\Command;
use IronGate\Pkgtrends\TrendsProvider;

class ClearTrendsQuery extends Command
{
    protected $signature   = 'pkgtrends:clear-trends-query {packagesQuery}';
    protected $description = 'Clear the cache of a trends query.';

    public function handle(): void
    {
        (new TrendsProvider($this->argument('packagesQuery')))->clearTrendsCache();

        $this->info('Cleared trends cache for query: ' . $this->argument('packagesQuery'));
    }
}
