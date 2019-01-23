<?php

namespace IronGate\Pkgtrends\Console\Commands\Import\PyPI;

use DOMDocument;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use IronGate\Pkgtrends\Models\Packages\PyPI;

class Packages extends Command
{
    protected $signature = 'import:pypi:packages';

    protected $description = 'Import data from the PyPI simple API.';

    public function handle(): void
    {
        // The HTTP client we are going to use to retrieve package information
        $client = new Client(['base_uri' => 'https://pypi.org/pypi/']);

        // The full package list (contains only package names)
        $document = new DOMDocument;
        $document->loadHTML(file_get_contents('https://pypi.org/simple/'));

        $promises = [];

        // Find all <a> elements which are linked to the package pages
        foreach ($document->getElementsByTagName('a') as $element) {
            // Create a async promise to retrieve the package information
            $promises[$element->nodeValue] = $client->getAsync("{$element->nodeValue}/json");

            // Queue up 25 promises before waiting for them to complete
            if (count($promises) < 25) {
                continue;
            }

            // Wait for all promises to return a result
            $results = \GuzzleHttp\Promise\settle($promises)->wait();

            // Loop over the results
            foreach ($results as $package => $result) {
                if ($result['state'] === 'fulfilled') {
                    $response = $result['value'];

                    // Make sure the response code is good
                    if ($response->getStatusCode() === 200) {
                        // Either find the package in the database or create a new instance
                        /** @var \IronGate\Pkgtrends\Models\Packages\PyPI $localPackage */
                        $localPackage = PyPI::query()->findOrNew((string)$package);

                        $localPackage->project = (string)$package;

                        // Retrieve and decode the package info
                        $info = rescue(function () use ($response) {
                            return json_decode($response->getBody()->getContents(), true) ?? [];
                        }, []);

                        // Either update with the new summary or use the old summary
                        $localPackage->description = array_get($info, 'info.summary', $localPackage->description);

                        // Either store or update the updated at timestamp
                        !$localPackage->exists || $localPackage->isDirty() ? $localPackage->save() : $localPackage->touch();
                    }
                }
            }

            $promises = [];
        }

        // Fire the health check url
        if (!empty(config('app.ping.import.pypi.packages'))) {
            retry(3, function () {
                file_get_contents(config('app.ping.import.pypi.packages'));
            }, 15);
        }
    }
}
