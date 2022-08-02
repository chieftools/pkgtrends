<?php

namespace ChiefTools\Pkgtrends;

use DatePeriod;
use DateInterval;
use Carbon\Carbon;
use RuntimeException;
use Illuminate\Support\Collection;

class TrendsProvider
{
    /**
     * The start of the trends query period.
     *
     * @var \Carbon\Carbon
     */
    private Carbon $start;

    /**
     * The end of the trends query period.
     *
     * @var \Carbon\Carbon
     */
    private Carbon $end;

    /**
     * Holds the query data for in-memory caching.
     *
     * @var \Illuminate\Support\Collection|null
     */
    private ?Collection $data;

    /**
     * TrendsQuery constructor.
     *
     * @param string $query
     */
    public function __construct(
        private string $query,
    ) {
        // TODO: This should probably be user configurable at some point
        $this->start = Carbon::now()->subDays(27 * 7);
        $this->end   = Carbon::now()->subDay();
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

        return sha1($this->getData()->map(fn ($trend) => $trend['info']['id'])->sort());
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
        return collect($this->getPeriod())->chunk(7)->map(fn ($dates) => $dates->first()->format('d M Y'));
    }

    /**
     * Clear the trends cache for the query.
     */
    public function clearTrendsCache(): void
    {
        collect(explode('-vs-', $this->query))->take(16)->each(function ($dependency) {
            if (!str_contains($dependency, ':')) {
                return;
            }

            [$provider, $name] = explode(':', trim($dependency));

            cache()->forget("{$provider}:{$name}.info");
            cache()->forget("{$provider}:{$name}.stats");
        });
    }

    /**
     * Get the query period to retrieve data for.
     *
     * @return \DatePeriod
     */
    private function getPeriod(): DatePeriod
    {
        return new DatePeriod($this->start, DateInterval::createFromDateString('1 day'), $this->end);
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

            $repository = self::getRepository($provider);

            if ($repository === null) {
                return [$dependency => null];
            }

            $package = cache()->remember("{$provider}:{$name}.info", now()->addHours(6), fn () => $repository->getPackage($name));

            $statistics = cache()->remember("{$provider}:{$name}.stats", now()->addHours(4), fn () => $repository->getPackageStats($name, $this->start, $this->end));

            if (empty($package) || empty($statistics)) {
                return [$dependency => null];
            }

            $stats = collect($this->getPeriod())
                ->mapWithKeys(fn ($date) => [$date->format('Y-m-d') => 0])
                ->merge($statistics)
                ->values()
                ->chunk(7)
                ->map(fn ($values) => $values->sum());

            return [
                $dependency => [
                    'info'  => $package,
                    'stats' => $stats,
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
        return $this->getData()->map(fn (array $dependency) => $dependency['info']['name_formatted'])->implode(' vs ');
    }

    /**
     * Return the package repositories.
     *
     * @param string $key
     *
     * @return \ChiefTools\Pkgtrends\Repositories\PackageRepository|null
     */
    public static function getRepository(string $key): ?Repositories\PackageRepository
    {
        return self::getRepositories()->first(fn (Repositories\PackageRepository $repository) => $repository::getKey() === $key);
    }

    /**
     * Return the package repositories.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getRepositories(): Collection
    {
        return collect(config('app.sources'))->map(fn ($source) => app()->make($source));
    }
}
