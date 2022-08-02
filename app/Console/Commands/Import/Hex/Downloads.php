<?php

namespace ChiefTools\Pkgtrends\Console\Commands\Import\Hex;

use Illuminate\Console\Command;
use ChiefTools\Pkgtrends\Jobs\Hex\ProcessPackageDownloads;

class Downloads extends Command
{
    protected $signature   = 'import:hex:downloads';
    protected $description = 'Import data from the Hex API.';

    public function handle(): void
    {
        $yesterday = now()->subDay()->format('Y-m-d');

        $this->info('Kicking of processing jobs for Hex downloads.');

        dispatch(new ProcessPackageDownloads($yesterday));
    }
}
