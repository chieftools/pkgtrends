<?php

namespace IronGate\Pkgtrends\Repositories;

use Carbon\Carbon;

class HexRepository extends PackageRepository
{
    /**
     * The package repository key.
     *
     * @var string
     */
    protected static $key = 'hex';

    /**
     * The font awesome icon for this repository.
     *
     * @var string
     */
    protected static $icon = 'fab fa-erlang';

    /**
     * The source used for this repository.
     *
     * @var array
     */
    protected static $sources = [
        'Hex' => 'https://hex.pm/',
    ];

    /**
     * The base uri used for the HTTP client.
     *
     * @var string
     */
    protected $baseUri = 'https://hex.pm/api/';

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
            $response = $this->http->get('packages', [
                'query' => [
                    'search'   => $query,
                    'per_page' => 10,
                ],
            ]);

            return collect(json_decode($response->getBody()->getContents(), true) ?? [])->map(function ($package) {
                return $this->formatHexPackage($package);
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
            $response = $this->http->get("packages/{$name}");

            $package = json_decode($response->getBody()->getContents(), true);

            return empty($package['name']) ? null : $this->formatHexPackage($package);
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
            $response = $this->http->get("packages/{$name}");

            $package = json_decode($response->getBody()->getContents(), true);

            return empty($package['name']) ? null : [Carbon::yesterday()->format('Y-m-d') => $package['downloads']['day'] ?? 0];
        }, null);
    }

    /**
     * Format the Packagist response to something we can use internally.
     *
     * @param array $package
     *
     * @return array
     */
    private function formatHexPackage(array $package): array
    {
        return [
            'id'             => self::getKey() . ":{$package['name']}",
            'name'           => $package['name'],
            'vendor'         => self::getKey(),
            'description'    => $package['meta']['description'],
            'permalink'      => "https://hex.pm/packages/{$package['name']}",
            'name_formatted' => "{$package['name']} (Erlang)",
        ];
    }
}
