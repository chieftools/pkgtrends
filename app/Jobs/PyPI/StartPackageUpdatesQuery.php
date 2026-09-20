<?php

namespace ChiefTools\Pkgtrends\Jobs\PyPI;

use Throwable;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use ChiefTools\Pkgtrends\Jobs\Concerns\LogsMessages;
use ChiefTools\Pkgtrends\Jobs\Concerns\RetriesWithBackoff;

class StartPackageUpdatesQuery implements ShouldQueue
{
    use InteractsWithQueue, Queueable, LogsMessages, RetriesWithBackoff;

    private const string RUN_CACHE_KEY   = 'import:pypi:packages:active-run';
    private const int    RUN_TTL_SECONDS = 7 * 24 * 60 * 60 - 60 * 60;

    public function __construct(
        private string $runId,
    ) {}

    public function handle(BigQueryClient $bigQuery): void
    {
        if (!self::isRunActive($this->runId)) {
            $this->logMessage("Skipping stale PyPI package metadata run:{$this->runId}.");

            return;
        }

        $query = $bigQuery->query(
            <<<'QUERY'
            SELECT
              project,
              summary
            FROM (
              SELECT
                REGEXP_REPLACE(LOWER(name), r'[-_.]+', '-') AS project,
                summary,
                ROW_NUMBER() OVER (
                  PARTITION BY REGEXP_REPLACE(LOWER(name), r'[-_.]+', '-')
                  ORDER BY upload_time DESC, filename DESC
                ) AS row_number
              FROM
                `bigquery-public-data.pypi.distribution_metadata`
            )
            WHERE
              row_number = 1
            QUERY,
        );

        $this->logMessage("Starting BigQuery package metadata query for run:{$this->runId}...");

        $job = $bigQuery->startQuery($query);

        $job->waitUntilComplete();

        if (!self::isRunActive($this->runId)) {
            $this->logMessage("Finished stale BigQuery package metadata query:{$job->id()}; discarding results.");

            return;
        }

        $this->logMessage("Finished BigQuery package metadata query:{$job->id()}; dispatching processing jobs.");

        dispatch(new ProcessPackageUpdates($job->id(), $this->runId));
    }

    public function failed(?Throwable $exception): void
    {
        self::releaseRun($this->runId);
    }

    public static function acquireRun(): ?string
    {
        $runId = (string)Str::uuid();

        return cache()->add(self::RUN_CACHE_KEY, $runId, self::RUN_TTL_SECONDS) ? $runId : null;
    }

    public static function isRunActive(string $runId): bool
    {
        return cache()->get(self::RUN_CACHE_KEY) === $runId;
    }

    public static function releaseRun(string $runId): void
    {
        if (self::isRunActive($runId)) {
            cache()->forget(self::RUN_CACHE_KEY);
        }
    }
}
