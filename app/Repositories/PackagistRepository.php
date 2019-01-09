<?php

namespace IronGate\Pkgtrends\Repositories;

use Carbon\Carbon;

class PackagistRepository extends PackageRepository
{
    /**
     * The package repository key.
     *
     * @var string
     */
    protected static $key = 'packagist';

    /**
     * The font awesome icon for this repository.
     *
     * @var string
     */
    protected static $icon = 'fab fa-php';

    /**
     * The source used for this repository.
     *
     * @var array
     */
    protected static $sources = [
        'Packagist' => 'https://packagist.org/',
    ];

    /**
     * The base uri used for the HTTP client.
     *
     * @var string
     */
    protected $baseUri = 'https://packagist.org/';

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
            $response = $this->http->get('search.json', [
                'query' => [
                    'q'        => $query,
                    'per_page' => 10,
                ],
            ]);

            return collect(json_decode($response->getBody()->getContents(), true)['results'] ?? [])->map(function ($package) {
                return $this->formatPackagistPackage($package);
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
        return rescue(function () use ($name) {
            $response = $this->http->get("packages/{$name}.json");

            $package = json_decode($response->getBody()->getContents(), true);

            return empty($package['package']) ? null : $this->formatPackagistPackage($package['package']);
        }, null);
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
            $response = $this->http->get("packages/{$name}/stats/all.json", [
                'query' => [
                    'average' => 'daily',
                    'from'    => $start->format('Y-m-d'),
                    'to'      => $end->format('Y-m-d'),
                ],
            ]);

            $stats = collect(json_decode($response->getBody()->getContents(), true) ?? []);

            return $stats->isEmpty() ? null : array_combine($stats->get('labels'), $stats->get('values'));
        }, null);
    }

    /**
     * Format the Packagist response to something we can use internally.
     *
     * @param array $package
     *
     * @return array
     */
    private function formatPackagistPackage(array $package): array
    {
        return [
            'id'             => self::getKey() . ":{$package['name']}",
            'name'           => $package['name'],
            'vendor'         => self::getKey(),
            'description'    => $package['description'],
            'permalink'      => "https://packagist.org/packages/{$package['name']}",
            'name_formatted' => "{$package['name']} (PHP)",
        ];
    }
}
