<?php

namespace IronGate\Pkgtrends\Repositories;

use Carbon\Carbon;

class WordPressRepository extends ExternalPackageRepository
{
    protected static string $key     = 'wordpress';
    protected static string $icon    = 'fab fa-wordpress';
    protected static array  $sources = [
        'WordPress.org' => 'https://wordpress.org/',
    ];

    protected string $baseUri = 'https://api.wordpress.org/';

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

    public function getPackage(string $name): ?array
    {
        return rescue(function () use ($name) {
            $response = $this->http->get("plugins/info/1.0/{$name}.json");

            $package = json_decode($response->getBody()->getContents(), true);

            return empty($package['slug']) ? null : $this->formatWordPressPackage($package);
        });
    }

    public function getPackageStats(string $name, Carbon $start, Carbon $end): ?array
    {
        return rescue(function () use ($name, $start) {
            $days = now()->diffInDays($start);

            $response = $this->http->get("stats/plugin/1.0/downloads.php?slug={$name}&limit={$days}");

            $stats = collect(json_decode($response->getBody()->getContents(), true) ?? []);

            return $stats->isEmpty() ? null : $stats->reverse()->all();
        });
    }

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
