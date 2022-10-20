<?php

namespace ChiefTools\Pkgtrends\Repositories;

use Carbon\Carbon;
use GuzzleHttp\Exception\ClientException;

class PackagistRepository extends ExternalPackageRepository
{
    protected static string $key     = 'packagist';
    protected static string $icon    = 'fab fa-php';
    protected static array  $sources = [
        'Packagist' => 'https://packagist.org/',
    ];

    protected string $baseUri = 'https://packagist.org/';

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

    public function getPackage(string $name): ?array
    {
        return rescue(function () use ($name) {
            try {
                $response = $this->http->get("packages/{$name}.json");

                $package = json_decode($response->getBody()->getContents(), true);

                return empty($package['package']) ? null : $this->formatPackagistPackage($package['package']);
            } catch (ClientException $e) {
                if ($e->getResponse()?->getStatusCode() === 404) {
                    return null;
                }

                throw $e;
            }
        });
    }

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

            return $stats->isEmpty() ? null : array_combine($stats->get('labels'), $stats->get('values')[$name]);
        });
    }

    private function formatPackagistPackage(array $package): array
    {
        return [
            'id'               => self::getKey() . ":{$package['name']}",
            'name'             => $package['name'],
            'vendor'           => self::getKey(),
            'description'      => $package['description'],
            'permalink'        => "https://packagist.org/packages/{$package['name']}",
            'name_formatted'   => "{$package['name']} (PHP)",
            'source_formatted' => 'Packagist (PHP)',
        ];
    }
}
