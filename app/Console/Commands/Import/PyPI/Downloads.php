<?php

namespace IronGate\Pkgtrends\Console\Commands\Import\PyPI;

use RuntimeException;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Google\Cloud\BigQuery\BigQueryClient;
use IronGate\Pkgtrends\Models\Stats\PyPI;

class Downloads extends Command
{
    protected $signature = 'import:pypi:downloads { --from=1 : how many days back } { --to=1 : to how many days back }';

    protected $description = 'Import data from PyPI BigQuery datasets.';

    public function handle(): void
    {
        // Extract the range from the CLI options passed
        $fromDays = (int)$this->option('from');
        $toDays   = (int)$this->option('to');

        // Make sure the range is a good range
        if ($fromDays < $toDays) {
            throw new RuntimeException('You should specify either the same or a larger --from number than --to.');
        }

        // Configure the Google Big Query client
        $bigQuery = new BigQueryClient([
            'projectId'   => 'package-trends',
            'keyFilePath' => storage_path('creds/google-bigquery.json'),
        ]);

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

        // Run the query and page the result by 1000 to prevent memory related issues
        foreach ($bigQuery->runQuery($query, ['maxResults' => 1000]) as $row) {
            try {
                // Insert the download count into the database
                (new PyPI(['date' => $row['yyyymmdd'], 'project' => $row['project'], 'downloads' => $row['downloads']]))->save();
            } catch (QueryException $e) {
                // Ignore them all, this is mostly here to ignore duplicates (which are not allowed)
            }
        }

        // If the range has been changed presume we are not running in the cron and therefore should not ping the healthcheck url
        if ($fromDays === 1 && $toDays === 1 && !empty(config('app.ping.import.pypi.downloads'))) {
            retry(3, function () {
                file_get_contents(config('app.ping.import.pypi.downloads'));
            }, 15);
        }
    }
}
