<?php

namespace IronGate\Pkgtrends\Jobs\Hex;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Database\QueryException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use IronGate\Pkgtrends\Models\Stats\Hex as HexStats;
use IronGate\Pkgtrends\Models\Packages\Hex as HexPackage;

class ProcessPackageDownloads implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    /**
     * @var string
     */
    protected $date;

    /**
     * @var int
     */
    protected $page;

    public function __construct($date, $page = 1)
    {
        $this->date = $date;
        $this->page = $page;
    }

    public function handle(): void
    {
        $client = new Client(['base_uri' => 'https://hex.pm/api/']);

        $response = retry(3, function () use ($client) {
            $response = $client->get('packages', [
                'query' => [
                    'page' => $this->page,
                    'sort' => '-inserted_at',
                ],
            ])->getBody()->getContents();

            return json_decode($response, true);
        }, 60);

        $processed = 0;

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
                        'date'      => $this->date,
                        'package'   => $name,
                        'downloads' => array_get($package, 'downloads.day', 0),
                    ]))->save();
                } catch (QueryException $e) {
                    // Ignore possible duplicates
                }
            }

            $processed++;
        }

        if ($processed > 0) {
            dispatch(new self($this->date, $this->page + 1));

            return;
        }

        $this->pingForCompletion();
    }

    private function pingForCompletion(): void
    {
        if (!empty(config('app.ping.import.hex.downloads'))) {
            retry(3, function () {
                file_get_contents(config('app.ping.import.hex.downloads'));
            }, 15);
        }
    }
}
