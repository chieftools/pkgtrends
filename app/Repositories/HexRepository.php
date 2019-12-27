<?php

namespace IronGate\Pkgtrends\Repositories;

use Carbon\Carbon;
use IronGate\Pkgtrends\Models\Packages;
use IronGate\Pkgtrends\Models\Stats;

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
            return Packages\Hex::query()->selectRaw("*, MATCH(`name`) AGAINST ('{$query}' IN NATURAL LANGUAGE MODE) as `score`")->orderByDesc('score')->take(100)->get()->take(10)->map(function (Packages\Hex $package) {
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
            $package = Packages\Hex::query()->where('name', '=', $name)->first();

            return empty($package) ? null : $this->formatHexPackage($package);
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
            $stats = Stats\Hex::query()->where('package', '=', $name)->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->get();

            return $stats->isEmpty() ? null : $stats->keyBy('date')->map(function (Stats\Hex $stat) {
                return $stat->downloads;
            })->all();
        });
    }

    /**
     * Format the Packagist response to something we can use internally.
     *
     * @param array $package
     *
     * @return array
     */
    private function formatHexPackage(Packages\Hex $package): array
    {
        return [
            'id'               => self::getKey().":{$package->name}",
            'name'             => $package->name,
            'vendor'           => self::getKey(),
            'description'      => $package->description,
            'permalink'        => "https://hex.pm/packages/{$package->name}",
            'name_formatted'   => "{$package->name} (Erlang)",
            'source_formatted' => 'Hex (Erlang)',
        ];
    }
}
