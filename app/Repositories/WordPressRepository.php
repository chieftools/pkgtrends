<?php

namespace IronGate\Pkgtrends\Repositories;

use Carbon\Carbon;

class WordPressRepository extends PackageRepository
{
    /**
     * The package repository key.
     *
     * @var string
     */
    protected static $key = 'wordpress';

    /**
     * The font awesome icon for this repository.
     *
     * @var string
     */
    protected static $icon = 'fab fa-wordpress';

    /**
     * The source used for this repository.
     *
     * @var array
     */
    protected static $sources = [
        'WordPress.org' => 'https://wordpress.org/',
    ];

    /**
     * The base uri used for the HTTP client.
     *
     * @var string
     */
    protected $baseUri = 'https://api.wordpress.org/';

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
            $response = $this->http->post('plugins/info/1.1/', [
                'form_params' => [
                    'action'  => 'query_plugins',
                    'request' => [
                        'search'   => $query,
                        'per_page' => 10,
                    ],
                ],
            ]);

            return collect(json_decode($response->getBody()->getContents(), true)['plugins'] ?? [])->map(function ($package) {
                return $this->formatWordPressPackage($package);
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
            $response = $this->http->get("plugins/info/1.0/{$name}.json");

            $package = json_decode($response->getBody()->getContents(), true);

            return empty($package['slug']) ? null : $this->formatWordPressPackage($package);
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
        return rescue(function () use ($name, $start) {
            $days = now()->diffInDays($start);

            $response = $this->http->get("stats/plugin/1.0/downloads.php?slug={$name}&limit={$days}");

            $stats = collect(json_decode($response->getBody()->getContents(), true) ?? []);

            return $stats->isEmpty() ? null : $stats->reverse()->all();
        });
    }

    /**
     * Format the Packagist response to something we can use internally.
     *
     * @param array $package
     *
     * @return array
     */
    private function formatWordPressPackage(array $package): array
    {
        return [
            'id'               => self::getKey() . ":{$package['slug']}",
            'name'             => $package['name'],
            'vendor'           => self::getKey(),
            'description'      => $package['short_description'] ?? null,
            'permalink'        => "https://wordpress.org/plugins/{$package['slug']}/",
            'name_formatted'   => "{$package['name']} (WordPress)",
            'source_formatted' => 'WordPress Plugin (PHP)',
        ];
    }
}
