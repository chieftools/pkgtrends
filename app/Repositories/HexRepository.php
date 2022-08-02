<?php

namespace ChiefTools\Pkgtrends\Repositories;

use Carbon\Carbon;
use ChiefTools\Pkgtrends\Models\Stats;
use ChiefTools\Pkgtrends\Models\Packages;

class HexRepository extends PackageRepository
{
    protected static string $key     = 'hex';
    protected static string $icon    = 'fab fa-erlang';
    protected static array  $sources = [
        'Hex' => 'https://hex.pm/',
    ];

    public function searchPackage(string $query): array
    {
        return rescue(function () use ($query) {
            return Packages\Hex::query()->selectRaw("*, MATCH(`name`) AGAINST ('{$query}' IN NATURAL LANGUAGE MODE) as `score`")->orderByDesc('score')->take(10)->get()->map(function (Packages\Hex $package) {
                return $this->formatHexPackage($package);
            })->all();
        }, []);
    }

    public function getPackage(string $name): ?array
    {
        return rescue(function () use ($name) {
            $package = Packages\Hex::query()->where('name', '=', $name)->first();

            return empty($package) ? null : $this->formatHexPackage($package);
        });
    }

    public function getPackageStats(string $name, Carbon $start, Carbon $end): ?array
    {
        return rescue(function () use ($name, $start, $end) {
            $stats = Stats\Hex::query()->where('package', '=', $name)->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->get();

            return $stats->isEmpty() ? null : $stats->keyBy('date')->map(function (Stats\Hex $stat) {
                return $stat->downloads;
            })->all();
        });
    }

    private function formatHexPackage(Packages\Hex $package): array
    {
        return [
            'id'               => self::getKey() . ":{$package->name}",
            'name'             => $package->name,
            'vendor'           => self::getKey(),
            'description'      => $package->description,
            'permalink'        => "https://hex.pm/packages/{$package->name}",
            'name_formatted'   => "{$package->name} (Erlang)",
            'source_formatted' => 'Hex (Erlang)',
        ];
    }
}
