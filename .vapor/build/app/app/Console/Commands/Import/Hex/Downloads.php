<?php

namespace IronGate\Pkgtrends\Console\Commands\Import\Hex;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use IronGate\Pkgtrends\Models\Stats\Hex as HexStats;
use IronGate\Pkgtrends\Models\Packages\Hex as HexPackage;

class Downloads extends Command
{
    protected $signature = 'import:hex:downloads';

    protected $description = 'Import data from the Hex API.';

    public function handle(): void
    {
        $client = new Client(['base_uri' => 'https://hex.pm/api/']);

        $currentPage = 1;
        $yesterday   = now()->subDay()->format('Y-m-d');

        do {
            $this->info("Processing page:{$currentPage}");

            $response = retry(3, function () use ($client, $currentPage) {
                $response = $client->get('packages', [
                    'query' => [
                        'page' => $currentPage,
                        'sort' => '-inserted_at',
                    ],
                ])->getBody()->getContents();

                return json_decode($response, true);
            }, 60);

            $resultCount = count($response);

            foreach ($response as $package) {
                if (!empty($name = array_get($package, 'name'))) {
                    $description = array_get($package, 'meta.description');

                    /** @var \IronGate\Pkgtrends\Models\Packages\Hex $localPackage */
                    $localPackage = HexPackage::query()->firstOrCreate(compact('name'), compact('description'));

                    if (!$localPackage->wasRecentlyCreated) {
                        $localPackage->description = $description ?? $localPackage->description;

                        if ($localPackage->isDirty('description')) {
                            $localPackage->save();
                        } else {
                            $localPackage->touch();
                        }
                    }

                    try {
                        (new HexStats([
                            'date'      => $yesterday,
                            'package'   => $name,
                            'downloads' => array_get($package, 'downloads.day', 0),
                        ]))->save();
                    } catch (QueryException $e) {
                        // Ignore possible duplicates
                    }
                }
            }

            $this->info(" > processed {$resultCount} results");

            $currentPage++;
        } while ($resultCount > 0);

        if (!empty(config('app.ping.import.hex.downloads'))) {
            retry(3, function () {
                file_get_contents(config('app.ping.import.hex.downloads'));
            }, 15);
        }
    }
}
