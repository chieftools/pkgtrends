<?php

namespace ChiefTools\Pkgtrends\Repositories;

use Carbon\Carbon;
use GuzzleHttp\Client;

class NpmRepository extends ExternalPackageRepository
{
    protected static string $key     = 'npm';
    protected static string $icon    = 'fab fa-npm';
    protected static array  $sources = [
        'npm' => 'https://www.npmjs.com/',
    ];

    protected string $baseUri       = 'https://api.npmjs.org/';
    protected string $searchBaseUri = 'https://registry.npmjs.org/-/v1/';

    /**
     * A Guzzle HTTP client instance used for retrieving package information.
     */
    protected Client $searchHttp;

    public function __construct()
    {
        parent::__construct();

        $this->searchHttp = http($this->searchBaseUri);
    }

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

    public function getPackage(string $name): ?array
    {
        return rescue(function () use ($name) {
            $response = $this->searchHttp->get("/{$name}");

            $package = json_decode($response->getBody()->getContents(), true);

            return empty($package['_id']) ? null : $this->formatNpmPackage($package);
        });
    }

    public function getPackageStats(string $name, Carbon $start, Carbon $end): ?array
    {
        return rescue(function () use ($name, $start, $end) {
            $response = $this->http->get('downloads/range/' . $start->format('Y-m-d') . ':' . $end->format('Y-m-d') . '/' . $name);

            $downloads = collect(json_decode($response->getBody()->getContents(), true)['downloads'] ?? []);

            return $downloads->isEmpty() ? null : $downloads->keyBy('day')->map(function ($data) {
                return $data['downloads'];
            })->all();
        });
    }

    private function formatNpmPackage(array $package): array
    {
        return [
            'id'               => self::getKey() . ":{$package['name']}",
            'name'             => $package['name'],
            'vendor'           => self::getKey(),
            'description'      => $package['description'] ?? 'No description provided',
            'permalink'        => "https://www.npmjs.com/package/{$package['name']}",
            'name_formatted'   => "{$package['name']} (JS)",
            'source_formatted' => 'NPM (JavaScript)',
        ];
    }
}
