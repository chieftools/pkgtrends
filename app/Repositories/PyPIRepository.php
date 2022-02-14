<?php

namespace IronGate\Pkgtrends\Repositories;

use Carbon\Carbon;
use IronGate\Pkgtrends\Models\Stats;
use IronGate\Pkgtrends\Models\Packages;

class PyPIRepository extends PackageRepository
{
    protected static string $key     = 'pypi';
    protected static string $icon    = 'fab fa-python';
    protected static array  $sources = [
        'PyPI' => 'https://pypi.org/',
    ];

    public function searchPackage(string $query): array
    {
        return rescue(function () use ($query) {
            return Packages\PyPI::query()->selectRaw("*, MATCH(`project`) AGAINST ('{$query}' IN NATURAL LANGUAGE MODE) as `score`")->orderByDesc('score')->take(10)->get()->map(function (Packages\PyPI $package) {
                return $this->formatPyPIPackage($package);
            })->all();
        }, []);
    }

    public function getPackage(string $name): ?array
    {
        return rescue(function () use ($name) {
            $package = Packages\PyPI::query()->where('project', '=', $name)->first();

            return empty($package) ? null : $this->formatPyPIPackage($package);
        });
    }

    public function getPackageStats(string $name, Carbon $start, Carbon $end): ?array
    {
        return rescue(function () use ($name, $start, $end) {
            $stats = Stats\PyPI::query()->where('project', '=', $name)->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->get();

            return $stats->isEmpty() ? null : $stats->keyBy('date')->map(function (Stats\PyPI $stat) {
                return $stat->downloads;
            })->all();
        });
    }

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
