<?php

namespace ChiefTools\Pkgtrends\Jobs\PyPI;

use GuzzleHttp\Promise\Utils;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use ChiefTools\Pkgtrends\Models\Packages\PyPI;
use ChiefTools\Pkgtrends\Jobs\Concerns\LogsMessages;

class ProcessPackageUpdates implements ShouldQueue
{
    use InteractsWithQueue, Queueable, LogsMessages;

    protected static int $perPage = 50;

    public function __construct(
        private int $page = 1
    ) {
    }

    public function handle(): void
    {
        $packages = PyPI::query()->orderBy('project')->skip(($this->page - 1) * self::$perPage)->take(self::$perPage)->get();

        if ($packages->isEmpty()) {
            $this->logMessage('Finished processing all pages!');

            $this->pingForCompletion();

            return;
        }

        $this->logMessage("Processing PyPI packages page:{$this->page}...");

        // The HTTP client we are going to use to retrieve package information
        $client = http('https://pypi.org/pypi/');

        $promises = [];

        // Fire of a API request for each package
        $packages->each(function (PyPI $package) use (&$promises, $client) {
            $promises[$package->project] = $client->getAsync("{$package->project}/json");
        });

        // Wait for all requests to finish
        $results = Utils::settle($promises)->wait();

        // Loop over the results
        foreach ($results as $package => $result) {
            /** @var \ChiefTools\Pkgtrends\Models\Packages\PyPI $localPackage */
            $localPackage = $packages->find($package);

            // Make sure the response was fullfilled and the response code is good
            if ($result['state'] === 'fulfilled' && ($response = $result['value'] ?? null) !== null && $response->getStatusCode() === 200) {
                // Retrieve and decode the package info
                $info = rescue(fn () => json_decode($response->getBody()->getContents(), true) ?? [], []);

                // Get the description from the response or use the old summary
                $description = array_get($info, 'info.summary', $localPackage->description);

                // Guard against inserting "UNKNOWN"
                $localPackage->description = $description === 'UNKNOWN' ? null : $description;

                // Persist changes if any
                $localPackage->save();
            }
        }

        $this->logMessage("Processed PyPI packages page:{$this->page}!");

        dispatch(new self($this->page + 1));
    }

    private function pingForCompletion(): void
    {
        if (!empty(config('app.ping.import.pypi.packages'))) {
            retry(3, static fn () => file_get_contents(config('app.ping.import.pypi.packages')), 15);
        }
    }
}
