<?php

namespace IronGate\Pkgtrends\Console\Commands\Import\PyPI;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use function GuzzleHttp\Promise\settle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use IronGate\Pkgtrends\Models\Packages\PyPI;

class Packages extends Command
{
    protected $signature = 'import:pypi:packages';

    protected $description = 'Import package summaries from the PyPI API.';

    public function handle(): void
    {
        // The HTTP client we are going to use to retrieve package information
        $client = new Client(['base_uri' => 'https://pypi.org/pypi/']);

        // Get all packages chunked in sets of 50 to not slam the PyPI API too hard
        PyPI::query()->chunk(50, function (Collection $packages) use ($client) {
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
        });

        // Fire the health check url
        if (!empty(config('app.ping.import.pypi.packages'))) {
            retry(3, function () {
                file_get_contents(config('app.ping.import.pypi.packages'));
            }, 15);
        }
    }
}
