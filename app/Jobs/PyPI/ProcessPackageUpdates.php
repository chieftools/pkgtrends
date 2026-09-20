<?php

namespace ChiefTools\Pkgtrends\Jobs\PyPI;

use Throwable;
use RuntimeException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use ChiefTools\Pkgtrends\Models\Packages\PyPI;
use ChiefTools\Pkgtrends\Jobs\Concerns\LogsMessages;
use ChiefTools\Pkgtrends\Jobs\Concerns\RetriesWithBackoff;

class ProcessPackageUpdates implements ShouldQueue
{
    use InteractsWithQueue, Queueable, LogsMessages, RetriesWithBackoff;

    private const int MAX_ROWS = 1000;

    private ?string $jobId;
    private ?string $runId;
    private int     $offset;
    private ?int    $page;

    // The final argument keeps pre-deployment queue payloads compatible.
    public function __construct(
        ?string $jobId = null,
        ?string $runId = null,
        int $offset = 0,
        ?int $page = null,
    ) {
        $this->jobId  = $jobId;
        $this->runId  = $runId;
        $this->offset = $offset;
        $this->page   = $page;
    }

    public function handle(BigQueryClient $bigQuery): void
    {
        if ($this->jobId === null || $this->runId === null) {
            $page = $this->page === null ? '' : " page:{$this->page}";

            $this->logMessage("Skipping legacy PyPI package update job{$page}.");

            return;
        }

        if (!StartPackageUpdatesQuery::isRunActive($this->runId)) {
            $this->logMessage("Skipping stale PyPI package update run:{$this->runId}.");

            return;
        }

        $bigQueryJob = $bigQuery->job($this->jobId);

        if (!$bigQueryJob->isComplete()) {
            throw new RuntimeException('The BigQuery package metadata job is not completed yet!');
        }

        $this->logMessage("Processing BigQuery package metadata job:{$this->jobId} with offset:{$this->offset}...");

        $processedRows = 0;
        $descriptions  = [];

        $queryResults = $bigQueryJob->queryResults([
            'maxResults' => self::MAX_ROWS,
            'startIndex' => $this->offset,
        ]);

        foreach ($queryResults->rows() as $row) {
            $processedRows++;

            $project     = $row['project'] ?? null;
            $description = $row['summary'] ?? null;

            if (!is_string($project) || $project === '') {
                continue;
            }

            $descriptions[$project] = is_string($description) && $description !== 'UNKNOWN' ? $description : null;
        }

        if ($processedRows === 0) {
            $this->logMessage('Finished processing all PyPI package metadata!');

            $this->pingForCompletion();

            StartPackageUpdatesQuery::releaseRun($this->runId);

            return;
        }

        $updatedPackages = 0;
        $packages        = PyPI::query()
            ->whereIn('project', array_keys($descriptions))
            ->get(['id', 'project', 'description']);

        foreach ($packages as $package) {
            $project = $this->normalizeProject($package->project);

            if (!array_key_exists($project, $descriptions)) {
                continue;
            }

            $package->description = $descriptions[$project];

            if (!$package->isDirty('description')) {
                continue;
            }

            $package->timestamps = false;
            $package->save();

            $updatedPackages++;
        }

        $this->logMessage("Processed {$processedRows} metadata rows and updated {$updatedPackages} packages for job:{$this->jobId} with offset:{$this->offset}.");

        if (StartPackageUpdatesQuery::isRunActive($this->runId)) {
            dispatch(new self($this->jobId, $this->runId, $this->offset + $processedRows));
        }
    }

    public function failed(?Throwable $exception): void
    {
        if ($this->runId !== null) {
            StartPackageUpdatesQuery::releaseRun($this->runId);
        }
    }

    private function normalizeProject(string $project): string
    {
        return strtolower(preg_replace('/[-_.]+/', '-', $project));
    }

    private function pingForCompletion(): void
    {
        if (!empty(config('app.ping.import.pypi.packages'))) {
            retry(3, static fn () => file_get_contents(config('app.ping.import.pypi.packages')), 15);
        }
    }
}
