<?php

namespace IronGate\Pkgtrends\Console\Commands\Import\PyPI;

use Illuminate\Console\Command;
use IronGate\Pkgtrends\Models\Stats\PyPI as PyPIStats;
use IronGate\Pkgtrends\Models\Packages\PyPI as PyPIPackages;

class Cleanup extends Command
{
    protected $signature = 'import:pypi:cleanup';

    protected $description = 'Cleanup old PyPI data we don\'t have a need for anymore.';

    public function handle(): void
    {
        // Delete all packages that we're not touched for 13 months since they're probably deleted
        $packages = PyPIPackages::query()->where('updated_at', '<', now()->subMonths(13))->delete();

        $this->info('Cleaned ' . $packages . ' packages');

        // Delete all stats older than 13 months (we only display 12 really)
        $stats = PyPIStats::query()->whereDate('date', '<', now()->subMonths(13))->delete();

        $this->info('Cleaned ' . $stats . ' stats');
    }
}
