<?php

namespace IronGate\Pkgtrends\Jobs\PyPI;

use RuntimeException;
use Illuminate\Bus\Queueable;
use Illuminate\Database\QueryException;
use Illuminate\Queue\InteractsWithQueue;
use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use IronGate\Pkgtrends\Jobs\Concerns\LogsMessages;
use IronGate\Pkgtrends\Models\Stats\PyPI as PyPIStat;
use IronGate\Pkgtrends\Models\Packages\PyPI as PyPIPackage;

class ProcessDownloadsQuery implements ShouldQueue
{
    use InteractsWithQueue, Queueable, LogsMessages;

    /**
     * @var string
     */
    protected $jobId;

    /**
     * @var int
     */
    protected $offset;

    /**
     * @var bool
     */
    protected $pingForCompletion;

    /**
     * @var int
     */
    protected static $maxRows = 1000;

    public function __construct(string $jobId, $offset = 0, $pingForCompletion = true)
    {
        $this->jobId             = $jobId;
        $this->offset            = $offset;
        $this->pingForCompletion = $pingForCompletion;
    }

    public function handle(BigQueryClient $bigQuery): void
    {
        $bigQueryJob = $bigQuery->job($this->jobId);

        // Make sure the query we are trying to process actually finished running
        if (!$bigQueryJob->isComplete()) {
            throw new RuntimeException('The BigQuery job we should process is not completed.');
        }

        $this->logMessage("Processing job:{$this->jobId} with offset:{$this->offset}...");

        $processedRows = 0;

        $queryResults = $bigQueryJob->queryResults([
            'maxResults' => self::$maxRows,
            'startIndex' => $this->offset,
        ]);

        foreach ($queryResults->rows() as $row) {
            // Make sure the package exists
            $package = PyPIPackage::query()->firstOrCreate(['project' => $row['project']]);

            // Update the package timestamp so we know the package is still actively being downloaded
            $package->touch();

            try {
                // Insert the download count into the database
                (new PyPIStat([
                    'date'      => $row['yyyymmdd'],
                    'project'   => $row['project'],
                    'downloads' => $row['downloads'],
                ]))->save();
            } catch (QueryException $e) {
                // Ignore them all, this is mostly here to ignore duplicates
            }

            $processedRows++;

            // To prevent taking too long bail out once we processed 1 page of data
            if ($processedRows >= self::$maxRows) {
                break;
            }
        }

        $this->logMessage("Processed {$processedRows} packages for job:{$this->jobId} with offset:{$this->offset}!");

        // If we processed more than 0 records assume there is more data to be processed so kick of another job starting where we left off
        if ($processedRows > 0) {
            dispatch(new self($this->jobId, $this->offset + $processedRows, $this->pingForCompletion));

            return;
        }

        $this->logMessage('Finished processing all pages!');

        $this->pingForCompletion();
    }

    private function pingForCompletion(): void
    {
        if ($this->pingForCompletion && !empty(config('app.ping.import.pypi.downloads'))) {
            retry(3, function () {
                file_get_contents(config('app.ping.import.pypi.downloads'));
            }, 15);
        }
    }
}
