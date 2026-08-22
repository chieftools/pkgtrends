<?php

namespace ChiefTools\Pkgtrends\Console\Commands\Import\PyPI;

use RuntimeException;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use ChiefTools\Pkgtrends\Models\Stats\PyPI as PyPIStats;
use ChiefTools\Pkgtrends\Models\Packages\PyPI as PyPIPackages;

class Cleanup extends Command
{
    private const int RetentionMonths       = 13;
    private const int FuturePartitionMonths = 2;

    protected $signature   = 'import:pypi:cleanup';
    protected $description = 'Cleanup old PyPI data we don\'t have a need for anymore.';

    public function handle(): void
    {
        $now             = CarbonImmutable::now();
        $retentionCutoff = $now->subMonthsNoOverflow(self::RetentionMonths);

        // Delete all packages that we're not touched for 13 months since they're probably deleted
        $packages = PyPIPackages::query()->where('updated_at', '<', $retentionCutoff)->delete();

        $this->info('Cleaned ' . $packages . ' packages');

        $partitions = $this->statisticsPartitions();

        if (!empty($partitions)) {
            $this->createFuturePartitions($partitions, $now);
            $this->dropExpiredPartitions($partitions, $retentionCutoff->startOfDay());
        }

        // A monthly partition can contain a few expired days while the rest of
        // that month remains inside the retention window.
        $stats = PyPIStats::query()->where('date', '<', $retentionCutoff->toDateString())->delete();

        $this->info('Cleaned ' . $stats . ' stats');
    }

    /** @return array<int, object{partition_name: string, partition_description: string}> */
    private function statisticsPartitions(): array
    {
        return DB::select(<<<'SQL'
            SELECT PARTITION_NAME AS partition_name,
                   PARTITION_DESCRIPTION AS partition_description
            FROM information_schema.PARTITIONS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'stats_pypi'
              AND PARTITION_NAME IS NOT NULL
            ORDER BY PARTITION_ORDINAL_POSITION
            SQL);
    }

    /** @param array<int, object{partition_name: string, partition_description: string}> $partitions */
    private function createFuturePartitions(array $partitions, CarbonImmutable $now): void
    {
        $futurePartitionCount = count(array_filter(
            $partitions,
            static fn (object $partition): bool => $partition->partition_name === 'p_future'
                && $partition->partition_description === 'MAXVALUE',
        ));

        if ($futurePartitionCount !== 1) {
            throw new RuntimeException('stats_pypi must have exactly one p_future MAXVALUE partition.');
        }

        $finitePartitions = array_values(array_filter(
            $partitions,
            static fn (object $partition): bool => $partition->partition_description !== 'MAXVALUE',
        ));

        if (empty($finitePartitions)) {
            throw new RuntimeException('stats_pypi does not have a finite monthly partition.');
        }

        $lastPartition   = $finitePartitions[array_key_last($finitePartitions)];
        $nextMonth       = $this->partitionUpperBound($lastPartition);
        $desiredBoundary = $now->startOfMonth()->addMonthsNoOverflow(self::FuturePartitionMonths + 1);
        $newPartitions   = [];
        $newNames        = [];

        while ($nextMonth->lessThan($desiredBoundary)) {
            $upperBound = $nextMonth->addMonthNoOverflow();
            $name       = 'p' . $nextMonth->format('Ym');

            $newNames[]      = $name;
            $newPartitions[] = sprintf(
                "    PARTITION `%s` VALUES LESS THAN ('%s')",
                $name,
                $upperBound->format('Y-m-d'),
            );
            $nextMonth       = $upperBound;
        }

        if (empty($newPartitions)) {
            return;
        }

        $newPartitions[] = '    PARTITION `p_future` VALUES LESS THAN (MAXVALUE)';

        DB::statement(sprintf(
            "ALTER TABLE stats_pypi REORGANIZE PARTITION `p_future` INTO (\n%s\n)",
            implode(",\n", $newPartitions),
        ));

        $this->info('Created future stats partitions: ' . implode(', ', $newNames));
    }

    /** @param array<int, object{partition_name: string, partition_description: string}> $partitions */
    private function dropExpiredPartitions(array $partitions, CarbonImmutable $retentionCutoff): void
    {
        $expiredPartitions = [];

        foreach ($partitions as $partition) {
            if ($partition->partition_description === 'MAXVALUE') {
                continue;
            }

            if ($this->partitionUpperBound($partition)->lessThanOrEqualTo($retentionCutoff)) {
                $expiredPartitions[] = $this->partitionName($partition);
            }
        }

        if (empty($expiredPartitions)) {
            return;
        }

        DB::statement(sprintf(
            'ALTER TABLE stats_pypi DROP PARTITION %s',
            implode(', ', array_map(static fn (string $name): string => "`{$name}`", $expiredPartitions)),
        ));

        $this->info('Dropped expired stats partitions: ' . implode(', ', $expiredPartitions));
    }

    /** @param object{partition_name: string, partition_description: string} $partition */
    private function partitionUpperBound(object $partition): CarbonImmutable
    {
        $value = trim($partition->partition_description, "'");
        $date  = CarbonImmutable::createFromFormat('!Y-m-d', $value);

        if ($date === null) {
            throw new RuntimeException("Invalid boundary for partition {$partition->partition_name}.");
        }

        return $date;
    }

    /** @param object{partition_name: string, partition_description: string} $partition */
    private function partitionName(object $partition): string
    {
        if (preg_match('/^p[0-9]{6}$/', $partition->partition_name) !== 1) {
            throw new RuntimeException("Invalid stats partition name {$partition->partition_name}.");
        }

        return $partition->partition_name;
    }
}
