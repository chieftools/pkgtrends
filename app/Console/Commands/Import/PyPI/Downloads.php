<?php

namespace IronGate\Pkgtrends\Console\Commands\Import\PyPI;

use RuntimeException;
use Illuminate\Console\Command;
use Google\Cloud\BigQuery\BigQueryClient;
use IronGate\Pkgtrends\Jobs\PyPI\ProcessDownloadsQuery;

class Downloads extends Command
{
    protected $signature = 'import:pypi:downloads { --from=1 : how many days back } { --to=1 : to how many days back }';

    protected $description = 'Import data from PyPI BigQuery datasets.';

    public function handle(BigQueryClient $bigQuery): void
    {
        // Extract the range from the CLI options passed
        $fromDays = (int)$this->option('from');
        $toDays   = (int)$this->option('to');

        // Make sure the range is a good range
        if ($fromDays < $toDays) {
            throw new RuntimeException('You should specify either the same or a larger --from number than --to.');
        }

        // Construct the query to find all PyPI projects and group them by project and date
        $query = $bigQuery->query(
            <<<QUERY
            SELECT
              FORMAT_TIMESTAMP("%Y-%m-%d", timestamp) AS yyyymmdd,
              COUNT(*) AS downloads,
              file.project AS project
            FROM
              `bigquery-public-data.pypi.file_downloads`
            WHERE
              DATE(timestamp) BETWEEN EXTRACT(DATE FROM DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL {$fromDays} DAY)) AND EXTRACT(DATE FROM DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL {$toDays} DAY))
              AND details.installer.name NOT IN ('bandersnatch', 'z3c.pypimirror', 'Artifactory', 'devpi')
            GROUP BY
              yyyymmdd,
              project
            QUERY
        );

        $this->info('Executing the BigQuery query...');

        $job = $bigQuery->startQuery($query);

        $job->waitUntilComplete();

        $this->info('Finished the BigQuery query and kicking of processing jobs.');

        dispatch(new ProcessDownloadsQuery($job->id(), 0, $fromDays === 1 && $toDays === 1));
    }
}
