<?php

namespace IronGate\Pkgtrends;

use DatePeriod;
use DateInterval;
use Carbon\Carbon;
use RuntimeException;
use Illuminate\Support\Collection;

class TrendsProvider
{
    /**
     * The packages query.
     *
     * @var string
     */
    private $query;

    /**
     * The start of the trends query period.
     *
     * @var \Carbon\Carbon
     */
    private $start;

    /**
     * The end of the trends query period.
     *
     * @var \Carbon\Carbon
     */
    private $end;

    /**
     * Holds the query data for in-memory caching.
     *
     * @var \Illuminate\Support\Collection|null
     */
    private $data;

    /**
     * TrendsQuery constructor.
     *
     * @param string $query
     */
    public function __construct(string $query)
    {
        $this->query = $query;

        // TODO: This should probably be user configurable at some time
        $this->start = Carbon::now()->subDays(27 * 7);
        $this->end   = Carbon::now()->subDays(1);
    }

    /**
     * Check if the provided query has valid packages.
     *
     * @return bool
     */
    public function hasData(): bool
    {
        return $this->getData()->isNotEmpty();
    }

    /**
     * Get the SHA1 hash for this query.
     *
     * @return string
     */
    public function getHash(): string
    {
        if (!$this->hasData()) {
            throw new RuntimeException('Cannot create a hash for empty dataset.');
        }

        return sha1($this->getData()->map(function ($trend) {
            return $trend['info']['id'];
        })->sort());
    }

    /**
     * Get the trends data for each package in the query.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getData(): Collection
    {
        return $this->data ?? $this->data = $this->buildTrendsData();
    }

    /**
     * Generate the graph labels for the query date range.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getGraphLabels(): Collection
    {
        return collect(new DatePeriod($this->start, DateInterval::createFromDateString('1 day'), $this->end))->chunk(7)->map(function ($dates) {
            return $dates->first()->format('d M Y');
        });
    }

    /**
     * Extract all packages from the query and retrieve the trends data for each.
     *
     * @return \Illuminate\Support\Collection
     */
    private function buildTrendsData(): Collection
    {
        return collect(explode('-vs-', $this->query))->take(16)->mapWithKeys(function ($dependency) {
            if (!str_contains($dependency, ':')) {
                return [$dependency => null];
            }

            [$provider, $name] = explode(':', trim($dependency));

            /** @var \IronGate\Pkgtrends\Repositories\PackageRepository $repository */
            $repository = self::getRepository($provider);

            if ($repository === null) {
                return [$dependency => null];
            }

            $package = cache()->remember("{$provider}:{$name}.info", 60 * 6, function () use ($repository, $name) {
                return $repository->getPackage($name);
            });

            $statistics = cache()->remember("{$provider}:{$name}.stats", 60 * 4, function () use ($repository, $name) {
                return $repository->getPackageStats($name, $this->start, $this->end);
            });

            if (empty($package) || empty($statistics)) {
                return [$dependency => null];
            }

            return [
                $dependency => [
                    'info'  => $package,
                    'stats' => collect(new DatePeriod($this->start, DateInterval::createFromDateString('1 day'), $this->end))->mapWithKeys(function ($date) {
                        return [$date->format('Y-m-d') => 0];
                    })->merge($statistics)->values()->chunk(7)->map(function ($values) {
                        return $values->sum();
                    }),
                ],
            ];
        })->filter();
    }

    /**
     * Get the formatted title for the query.
     *
     * @return string
     */
    public function getFormattedTitle(): string
    {
        return $this->getData()->map(function (array $dependency) {
            return $dependency['info']['name_formatted'];
        })->implode(' vs ');
    }

    /**
     * Return the package repositories.
     *
     * @param string $key
     *
     * @return \IronGate\Pkgtrends\Repositories\PackageRepository|null
     */
    public static function getRepository(string $key): ?Repositories\PackageRepository
    {
        return self::getRepositories()->first(function (Repositories\PackageRepository $repository) use ($key) {
            return $repository::getKey() === $key;
        });
    }

    /**
     * Return the package repositories.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getRepositories(): Collection
    {
        return collect(config('app.sources'))->map(function ($source) {
            return app()->make($source);
        });
    }
}
