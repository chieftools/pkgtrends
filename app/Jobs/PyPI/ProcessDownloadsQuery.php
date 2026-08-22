<?php

namespace ChiefTools\Pkgtrends\Jobs\PyPI;

use RuntimeException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use ChiefTools\Pkgtrends\Jobs\Concerns\LogsMessages;
use ChiefTools\Pkgtrends\Models\Stats\PyPI as PyPIStat;
use ChiefTools\Pkgtrends\Models\Packages\PyPI as PyPIPackage;

class ProcessDownloadsQuery implements ShouldQueue
{
    use InteractsWithQueue, Queueable, LogsMessages;

    private static int $maxRows = 1000;

    public function __construct(
        private string $jobId,
        private int $offset = 0,
        private bool $pingForCompletion = true,
    ) {}

    public function handle(BigQueryClient $bigQuery): void
    {
        $bigQueryJob = $bigQuery->job($this->jobId);

        // Make sure the query we are trying to process actually finished running
        if (!$bigQueryJob->isComplete()) {
            throw new RuntimeException('The BigQuery job we should process is not completed yet!');
        }

        $this->logMessage("Processing job:{$this->jobId} with offset:{$this->offset}...");

        $processedRows = 0;
        $now           = now();

        $packagesToInsert = [];
        $downloadRows     = [];
        $projectKeys      = [];

        $queryResults = $bigQueryJob->queryResults([
            'maxResults' => self::$maxRows,
            'startIndex' => $this->offset,
        ]);

        foreach ($queryResults->rows() as $row) {
            $processedRows++;

            $projectKey = strtolower($row['project']);

            $packagesToInsert[$projectKey] = [
                'project'    => $row['project'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $downloadRows[] = [
                'date'        => $row['yyyymmdd'],
                'project_key' => $projectKey,
                'downloads'   => $row['downloads'],
            ];

            $projectKeys[] = $row['project'];

            // To prevent timeouts we bail out once we processed 1 page of data
            if ($processedRows >= self::$maxRows) {
                break;
            }
        }

        // If we processed no rows we can assume we are done and have pocessed all data
        if ($processedRows === 0) {
            $this->logMessage('Finished processing all package download data!');

            $this->pingForCompletion();

            return;
        }

        $existingPackageKeys = PyPIPackage::query()
            ->whereIn('project', $projectKeys)
            ->pluck('project')
            ->mapWithKeys(static fn (string $project) => [strtolower($project) => true])
            ->all();

        $missingPackages = array_diff_key($packagesToInsert, $existingPackageKeys);

        if (!empty($missingPackages)) {
            PyPIPackage::query()->insertOrIgnore(array_values($missingPackages));
        }

        $packageIds = PyPIPackage::query()
            ->whereIn('project', $projectKeys)
            ->get(['id', 'project'])
            ->mapWithKeys(static fn (PyPIPackage $package) => [strtolower($package->project) => $package->id]);

        $statsToInsert = array_map(static function (array $row) use ($packageIds): array {
            $packageId = $packageIds->get($row['project_key']);

            if ($packageId === null) {
                throw new RuntimeException("Could not resolve package ID for {$row['project_key']}.");
            }

            return [
                'date'       => $row['date'],
                'package_id' => $packageId,
                'downloads'  => $row['downloads'],
            ];
        }, $downloadRows);

        // Insert all missing stats
        PyPIStat::query()->insertOrIgnore($statsToInsert);

        // Update the updated at timestamp to indicate the package is still downloaded
        PyPIPackage::query()->whereIn('project', $projectKeys)->update(['updated_at' => $now]);

        $this->logMessage("Processed {$processedRows} packages for job:{$this->jobId} with offset:{$this->offset}!");

        dispatch(new self($this->jobId, $this->offset + $processedRows, $this->pingForCompletion));
    }

    private function pingForCompletion(): void
    {
        if ($this->pingForCompletion && !empty(config('app.ping.import.pypi.downloads'))) {
            retry(3, static fn () => file_get_contents(config('app.ping.import.pypi.downloads')), 15);
        }
    }
}
