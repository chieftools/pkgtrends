<?php

namespace IronGate\Pkgtrends\Console\Commands\Import\Hex;

use Illuminate\Console\Command;
use IronGate\Pkgtrends\Models\Stats\Hex as HexStats;
use IronGate\Pkgtrends\Models\Packages\Hex as HexPackages;

class Cleanup extends Command
{
    protected $signature = 'import:hex:cleanup';

    protected $description = 'Cleanup old Hex data we don\'t have a need for anymore.';

    public function handle(): void
    {
        // Delete all packages that we're not touched for over a month since they're probably deleted
        $packages = HexPackages::query()->where('updated_at', '<', now()->subMonth())->delete();

        $this->info('Cleaned ' . $packages . ' packages');

        // Delete all stats older than 13 months (we only display 12 really)
        $stats = HexStats::query()->whereDate('date', '<', now()->subMonths(13))->delete();

        $this->info('Cleaned ' . $stats . ' stats');
    }
}
