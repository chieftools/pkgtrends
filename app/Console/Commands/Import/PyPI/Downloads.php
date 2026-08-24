<?php

namespace ChiefTools\Pkgtrends\Console\Commands\Import\PyPI;

use RuntimeException;
use Illuminate\Console\Command;
use ChiefTools\Pkgtrends\Jobs\PyPI\StartDownloadsQuery;

class Downloads extends Command
{
    protected $signature   = 'import:pypi:downloads { --from=1 : how many days back } { --to=1 : to how many days back }';
    protected $description = 'Import data from PyPI BigQuery datasets.';

    public function handle(): void
    {
        $fromDays = (int)$this->option('from');
        $toDays   = (int)$this->option('to');

        if ($fromDays < $toDays) {
            throw new RuntimeException('You should specify either the same or a larger --from number than --to.');
        }

        dispatch(new StartDownloadsQuery($fromDays, $toDays));

        $this->info('Queued the PyPI downloads import.');
    }
}
