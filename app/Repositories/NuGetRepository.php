<?php

namespace IronGate\Pkgtrends\Repositories;

use Carbon\Carbon;

class NuGetRepository extends PackageRepository
{
    /**
     * The package repository key.
     *
     * @var string
     */
    protected static $key = 'nuget';

    /**
     * The font awesome icon for this repository.
     *
     * @var string
     */
    protected static $icon = 'fab fa-microsoft';

    /**
     * The source used for this repository.
     *
     * @var array
     */
    protected static $sources = [
        'NuGet' => 'https://nugettrends.com/',
    ];

    /**
     * The base uri used for the HTTP client.
     *
     * @var string
     */
    protected $baseUri = 'https://nugettrends.com/api/package/';

    /**
     * Search for a package using a query.
     *
     * @param string $query
     *
     * @return array
     */
    public function searchPackage(string $query): array
    {
        return rescue(function () use ($query) {
            $response = $this->http->get('search', [
                'query' => [
                    'q' => $query,
                ],
            ]);

            return collect(json_decode($response->getBody()->getContents(), true) ?? [])->map(function ($package) {
                return $this->formatNuGetPackage($package);
            })->all();
        }, []);
    }

    /**
     * Get the package info using an exact package name.
     *
     * @param string $name
     *
     * @return array|null
     */
    public function getPackage(string $name): ?array
    {
        return collect($this->searchPackage($name))->first(function ($package) use ($name) {
            return $package['name'] === $name;
        });
    }

    /**
     * Retrieve the package stats for a exact package name.
     *
     * @param string         $name
     * @param \Carbon\Carbon $start
     * @param \Carbon\Carbon $end
     *
     * @return array
     */
    public function getPackageStats(string $name, Carbon $start, Carbon $end): ?array
    {
        return rescue(function () use ($name, $start, $end) {
            $response = $this->http->get("history/{$name}", [
                'query' => [
                    'months' => 6,
                ],
            ]);

            $stats = collect(json_decode($response->getBody()->getContents(), true)['downloads'] ?? [])->map(function ($stat) {
                return [
                    'date'  => Carbon::createFromFormat('Y-m-d\T00:00:00', $stat['date'])->format('Y-m-d'),
                    'value' => (int)$stat['count'],
                ];
            });

            return $stats->isEmpty() ? null : array_combine($stats->pluck('date')->all(), $stats->pluck('value')->all());
        }, null);
    }

    /**
     * Format the Packagist response to something we can use internally.
     *
     * @param array $package
     *
     * @return array
     */
    private function formatNuGetPackage(array $package): array
    {
        return [
            'id'             => self::getKey() . ":{$package['packageId']}",
            'name'           => $package['packageId'],
            'vendor'         => self::getKey(),
            'permalink'      => "https://www.nuget.org/packages/{$package['packageId']}",
            'formatted_name' => "{$package['name']} (.NET)",
        ];
    }
}
