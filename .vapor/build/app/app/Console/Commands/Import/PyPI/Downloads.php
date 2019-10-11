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
        $query = $bigQuery->query('
            SELECT
              STRFTIME_UTC_USEC(timestamp, "%Y-%m-%d") AS yyyymmdd,
              COUNT(*) AS downloads,
              file.project as project
            FROM
              TABLE_DATE_RANGE( [the-psf:pypi.downloads], DATE_ADD(CURRENT_TIMESTAMP(), -' . $fromDays . ', "day"), DATE_ADD(CURRENT_TIMESTAMP(), -' . $toDays . ', "day") )
            GROUP BY
              yyyymmdd,
              project;
        ')->useLegacySql(true);

        $this->info('Executing the BigQuery query...');

        $job = $bigQuery->startQuery($query);

        $job->waitUntilComplete();

        $this->info('Finished the BigQuery query and kicking of processing jobs.');

        dispatch(new ProcessDownloadsQuery($job->id(), 0, $fromDays === 1 && $toDays === 1));
    }
}
