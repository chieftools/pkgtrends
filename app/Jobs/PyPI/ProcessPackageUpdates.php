<?php

namespace IronGate\Pkgtrends\Jobs\PyPI;

use GuzzleHttp\Client;
use function GuzzleHttp\Promise\settle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use IronGate\Pkgtrends\Jobs\Concerns\LogsMessages;
use IronGate\Pkgtrends\Models\Packages\PyPI;

class ProcessPackageUpdates implements ShouldQueue
{
    use InteractsWithQueue, Queueable, LogsMessages;

    /**
     * @var int
     */
    protected $page;

    /**
     * @var int
     */
    protected static $perPage = 50;

    public function __construct($page = 1)
    {
        $this->page = $page;
    }

    public function handle(): void
    {
        $packages = PyPI::query()->orderBy('project')->skip(($this->page - 1) * self::$perPage)->take(self::$perPage)->get();

        if ($packages->isEmpty()) {
            $this->logMessage('Finished processing all pages!');

            $this->pingForCompletion();

            return;
        }

        $this->logMessage("Processing page:{$this->page}...");

        // The HTTP client we are going to use to retrieve package information
        $client = new Client(['base_uri' => 'https://pypi.org/pypi/']);

        $promises = [];

        // Fire of a API request for each package
        $packages->each(function (PyPI $package) use (&$promises, $client) {
            $promises[$package->project] = $client->getAsync("{$package->project}/json");
        });

        // Wait for all requests to finish
        $results = settle($promises)->wait();

        // Loop over the results
        foreach ($results as $package => $result) {
            /** @var \IronGate\Pkgtrends\Models\Packages\PyPI $localPackage */
            $localPackage = $packages->find($package);

            // Make sure the response was fullfilled and the response code is good
            if ($result['state'] === 'fulfilled' && ($response = $result['value'] ?? null) !== null && $response->getStatusCode() === 200) {
                // Retrieve and decode the package info
                $info = rescue(function () use ($response) {
                    return json_decode($response->getBody()->getContents(), true) ?? [];
                }, []);

                // Get the description from the response or use the old summary
                $description = array_get($info, 'info.summary', $localPackage->description);

                // Guard against inserting "UNKNOWN"
                $localPackage->description = $description === 'UNKNOWN' ? null : $description;

                // Persist changes if any
                $localPackage->save();
            }
        }

        $this->logMessage("Processed page:{$this->page}!");

        dispatch(new self($this->page + 1));
    }

    private function pingForCompletion(): void
    {
        if (!empty(config('app.ping.import.pypi.packages'))) {
            retry(3, function () {
                file_get_contents(config('app.ping.import.pypi.packages'));
            }, 15);
        }
    }
}
