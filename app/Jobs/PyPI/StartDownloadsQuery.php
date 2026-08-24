<?php

namespace ChiefTools\Pkgtrends\Jobs\PyPI;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use ChiefTools\Pkgtrends\Jobs\Concerns\LogsMessages;

class StartDownloadsQuery implements ShouldQueue
{
    use InteractsWithQueue, Queueable, LogsMessages;

    public function __construct(
        private int $fromDays,
        private int $toDays,
    ) {}

    public function handle(BigQueryClient $bigQuery): void
    {
        $query = $bigQuery->query(
            <<<QUERY
            SELECT
              FORMAT_TIMESTAMP("%Y-%m-%d", timestamp) AS yyyymmdd,
              COUNT(*) AS downloads,
              file.project AS project
            FROM
              `bigquery-public-data.pypi.file_downloads`
            WHERE
              DATE(timestamp) BETWEEN EXTRACT(DATE FROM DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL {$this->fromDays} DAY)) AND EXTRACT(DATE FROM DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL {$this->toDays} DAY))
              AND details.installer.name NOT IN ('bandersnatch', 'z3c.pypimirror', 'Artifactory', 'devpi')
            GROUP BY
              yyyymmdd,
              project
            QUERY
        );

        $this->logMessage("Starting BigQuery downloads query for {$this->fromDays} to {$this->toDays} days ago...");

        $job = $bigQuery->startQuery($query);

        $job->waitUntilComplete();

        $this->logMessage("Finished BigQuery downloads query:{$job->id()}; dispatching processing jobs.");

        dispatch(new ProcessDownloadsQuery(
            $job->id(),
            pingForCompletion: $this->fromDays === 1 && $this->toDays === 1,
        ));
    }
}
