<?php

namespace ChiefTools\Pkgtrends\Console\Commands\Import\PyPI;

use Throwable;
use Illuminate\Console\Command;
use ChiefTools\Pkgtrends\Jobs\PyPI\StartPackageUpdatesQuery;

class Packages extends Command
{
    protected $signature   = 'import:pypi:packages';
    protected $description = 'Import package summaries from the PyPI BigQuery dataset.';

    public function handle(): void
    {
        $runId = StartPackageUpdatesQuery::acquireRun();

        if ($runId === null) {
            $this->info('A PyPI package metadata import is already running.');

            return;
        }

        try {
            dispatch(new StartPackageUpdatesQuery($runId));
        } catch (Throwable $exception) {
            StartPackageUpdatesQuery::releaseRun($runId);

            throw $exception;
        }

        $this->info('Queued the PyPI package metadata import.');
    }
}
