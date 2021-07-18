<?php

namespace IronGate\Pkgtrends\Console\Commands\Import\PyPI;

use Illuminate\Console\Command;
use IronGate\Pkgtrends\Jobs\PyPI\ProcessPackageUpdates;

class Packages extends Command
{
    protected $signature   = 'import:pypi:packages';
    protected $description = 'Import package summaries from the PyPI API.';

    public function handle(): void
    {
        dispatch(new ProcessPackageUpdates);
    }
}
