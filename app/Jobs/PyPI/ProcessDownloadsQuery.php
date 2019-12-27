<?php

namespace IronGate\Pkgtrends\Jobs\PyPI;

use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use IronGate\Pkgtrends\Jobs\Concerns\LogsMessages;
use IronGate\Pkgtrends\Models\Packages\PyPI as PyPIPackage;
use IronGate\Pkgtrends\Models\Stats\PyPI as PyPIStat;
use RuntimeException;

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
        $this->jobId = $jobId;
        $this->offset = $offset;
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

        $packagesToInsert = [];
        $statsToInsert = [];
        $projectKeys = [];

        foreach ($queryResults->rows() as $row) {
            // Collect all the packages
            $packagesToInsert[] = [
                'project'    => $row['project'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Collect all the stats
            $statsToInsert[] = [
                'date'      => $row['yyyymmdd'],
                'project'   => $row['project'],
                'downloads' => $row['downloads'],
            ];

            // Collect all the project keys
            $projectKeys[] = $row['project'];

            $processedRows++;

            // To prevent taking too long bail out once we processed 1 page of data
            if ($processedRows >= self::$maxRows) {
                break;
            }
        }

        // Insert all missing packages
        PyPIPackage::query()->insertOrIgnore($packagesToInsert);

        // Update the updated at timestamp to indicate the package is still downloaded
        PyPIPackage::query()->whereIn('project', $projectKeys)->update([
            'updated_at' => now(),
        ]);

        // Insert all missing stats
        PyPIStat::query()->insertOrIgnore($statsToInsert);

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
