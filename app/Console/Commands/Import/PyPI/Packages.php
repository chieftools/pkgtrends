<?php

namespace IronGate\Pkgtrends\Console\Commands\Import\PyPI;

use DOMDocument;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use IronGate\Pkgtrends\Packages\PyPI;

class Packages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:pypi:packages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from the PyPI simple API.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
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
                        $localPackage = PyPI::query()->findOrNew((string)$package);

                        // Json decode the package info
                        $info = rescue(function () use ($response) {
                            return json_decode($response->getBody()->getContents(), true) ?? [];
                        }, []);

                        // If the summary is empty something probably went wrong, so discard the update
                        if (!empty(array_get($info, 'info.summary'))) {
                            $localPackage->project     = (string)$package;
                            $localPackage->description = array_get($info, 'info.summary');

                            // Store the package in the database, if nothing has changed nothing gets updated
                            $localPackage->save();
                        }
                    }
                }
            }

            $promises = [];
        }

        // Fire the health check url
        if (!empty(config('app.ping.import.pypi.packages'))) {
            file_get_contents(config('app.ping.import.pypi.packages'));
        }
    }
}
