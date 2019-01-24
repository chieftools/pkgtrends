<?php

namespace IronGate\Pkgtrends\Repositories;

use Carbon\Carbon;
use IronGate\Pkgtrends\Models\Stats;
use IronGate\Pkgtrends\Models\Packages;

class PyPIRepository extends PackageRepository
{
    /**
     * The package repository key.
     *
     * @var string
     */
    protected static $key = 'pypi';

    /**
     * The font awesome icon for this repository.
     *
     * @var string
     */
    protected static $icon = 'fab fa-python';

    /**
     * The source used for this repository.
     *
     * @var array
     */
    protected static $sources = [
        'PyPI' => 'https://pypi.org/',
    ];

    /**
     * The base uri used for the HTTP client.
     *
     * @var string
     */
    protected $baseUri = 'https://pypi.org/';

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
            return Packages\PyPI::query()->selectRaw("*, MATCH(`project`) AGAINST ('{$query}' IN NATURAL LANGUAGE MODE) as `score`")->orderByDesc('score')->take(100)->get()->take(10)->map(function (Packages\PyPI $package) {
                return $this->formatPyPIPackage($package);
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
            $package = Packages\PyPI::query()->where('project', '=', $name)->first();

            return empty($package) ? null : $this->formatPyPIPackage($package);
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
            $stats = Stats\PyPI::query()->where('project', '=', $name)->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->get();

            return $stats->isEmpty() ? null : $stats->keyBy('date')->map(function (Stats\PyPI $stat) {
                return $stat->downloads;
            })->all();
        });
    }

    /**
     * Format the PyPI response to something we can use internally.
     *
     * @param \IronGate\Pkgtrends\Models\Packages\PyPI $package
     *
     * @return array
     */
    private function formatPyPIPackage(Packages\PyPI $package): array
    {
        return [
            'id'               => self::getKey() . ":{$package->project}",
            'name'             => $package->project,
            'vendor'           => self::getKey(),
            'description'      => $package->description,
            'permalink'        => "https://pypi.org/project/{$package->project}",
            'name_formatted'   => "{$package->project} (Python)",
            'source_formatted' => 'PyPI (Python)',
        ];
    }
}
