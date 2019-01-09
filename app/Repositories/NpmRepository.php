<?php

namespace IronGate\Pkgtrends\Repositories;

use Carbon\Carbon;
use GuzzleHttp\Client;

class NpmRepository extends PackageRepository
{
    /**
     * The package repository key.
     *
     * @var string
     */
    protected static $key = 'npm';

    /**
     * The font awesome icon for this repository.
     *
     * @var string
     */
    protected static $icon = 'fab fa-npm';

    /**
     * The source used for this repository.
     *
     * @var array
     */
    protected static $sources = [
        'npm' => 'https://www.npmjs.com/',
    ];

    /**
     * The base uri used for the HTTP client.
     *
     * @var string
     */
    protected $baseUri = 'https://api.npmjs.org/';

    /**
     * The base uri used for the HTTP client used for retrieving package information.
     *
     * @var string
     */
    protected $searchBaseUri = 'https://registry.npmjs.org/-/v1/';

    /**
     * A Guzzle HTTP client instance used for retrieving package information.
     *
     * @var \GuzzleHttp\Client
     */
    protected $searchHttp;

    /**
     * NpmRepository constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->searchHttp = new Client(['base_uri' => $this->searchBaseUri]);
    }

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
            $response = $this->searchHttp->get('search', [
                'query' => [
                    'text' => $query,
                    'size' => 10,
                ],
            ]);

            return collect(json_decode($response->getBody()->getContents(), true)['objects'] ?? [])->map(function ($result) {
                return $result['package'];
            })->map(function ($package) {
                return $this->formatNpmPackage($package);
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
            $response = $this->searchHttp->get("/{$name}");

            $package = json_decode($response->getBody()->getContents(), true);

            return empty($package['_id']) ? null : $this->formatNpmPackage($package);
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
            $response = $this->http->get('downloads/range/' . $start->format('Y-m-d') . ':' . $end->format('Y-m-d') . '/' . $name);

            $downloads = collect(json_decode($response->getBody()->getContents(), true)['downloads'] ?? []);

            return $downloads->isEmpty() ? null : $downloads->keyBy('day')->map(function ($data) {
                return $data['downloads'];
            })->all();
        }, null);
    }

    /**
     * Format the npm response to something we can use internally.
     *
     * @param array $package
     *
     * @return array
     */
    private function formatNpmPackage(array $package): array
    {
        return [
            'id'             => self::getKey() . ":{$package['name']}",
            'name'           => $package['name'],
            'vendor'         => self::getKey(),
            'description'    => $package['description'] ?? 'No description provided',
            'permalink'      => "https://www.npmjs.com/package/{$package['name']}",
            'name_formatted' => "{$package['name']} (JS)",
        ];
    }
}
