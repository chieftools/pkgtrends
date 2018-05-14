<?php

namespace IronGate\Pkgtrends\Http\Controllers;

use DatePeriod;
use DateInterval;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use IronGate\Pkgtrends\Repositories;

class TrendsController extends Controller
{
    /**
     * Show the trends view.
     *
     * @param string $packages
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function showTrends($packages = null)
    {
        // If there are no packages just show an empty view
        if (empty($packages)) {
            return view('trends.index');
        }

        // This should probably be configurable at some time
        $start = Carbon::now()->subDays(27 * 7);
        $end   = Carbon::now()->subDays(1);

        // Generate the graph labels
        $statLabels = collect(new DatePeriod($start, DateInterval::createFromDateString('1 day'), $end))->chunk(7)->map(function ($dates) {
            return $dates->first()->format('d M Y');
        });

        // Explode the path and extract all the dependencies from it
        $dependencies = collect(explode('-vs-', $packages))->take(16)->mapWithKeys(function ($dependency) use ($start, $end) {
            if (!str_contains($dependency, ':')) {
                return [$dependency => null];
            }

            [$provider, $name] = explode(':', trim($dependency));

            /** @var \IronGate\Pkgtrends\Repositories\PackageRepository $repository */
            $repository = $this->getRepository($provider);

            if ($repository === null) {
                return [$dependency => null];
            }

            $package = cache()->remember("{$provider}:{$name}.info", 60 * 6, function () use ($repository, $name) {
                return $repository->getPackage($name);
            });

            $statistics = cache()->remember("{$provider}:{$name}.stats", 60 * 4, function () use ($repository, $name, $start, $end) {
                return $repository->getPackageStats($name, $start, $end);
            });

            if (empty($package) || empty($statistics)) {
                return [$dependency => null];
            }

            return [
                $dependency => [
                    'info'  => $package,
                    'stats' => collect(new DatePeriod($start, DateInterval::createFromDateString('1 day'), $end))->mapWithKeys(function ($date) {
                        return [$date->format('Y-m-d') => 0];
                    })->merge($statistics)->values()->chunk(7)->map(function ($values) {
                        return $values->sum();
                    }),
                ],
            ];
        })->filter();

        // If we could not find data for any of the dependencies (could be bogus data for example) return to the homepage
        if ($dependencies->isEmpty()) {
            return redirect()->action('TrendsController@showTrends');
        }

        // Build a "nice" page title
        $title = $dependencies->map(function (array $dependency) {
            return $this->getRepository($dependency['info']['vendor'])->formatPackageName($dependency['info']);
        })->implode(' vs ');

        // Build a list of all vendors and their icons
        $vendors = collect(config('app.sources'))->mapWithKeys(function ($source) {
            return [$source::getKey() => $source::getIcon()];
        });

        return view('trends.index', compact('title', 'dependencies', 'statLabels', 'vendors'));
    }

    /**
     * Search all the repositories for packages matching the user query.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function searchPackages(Request $request): array
    {
        $query = trim(urldecode($request->get('query')));

        if (empty($query)) {
            return [];
        }

        return $this->getRepositories()->flatMap(function (Repositories\PackageRepository $repository) use ($query) {
            return cache()->remember("{$repository::getKey()}:query:" . str_slug($query), 60, function () use ($repository, $query) {
                return $repository->searchPackage($query);
            });
        })->values()->all();
    }


    /**
     * Return the package repositories.
     *
     * @param string $key
     *
     * @return \IronGate\Pkgtrends\Repositories\PackageRepository|null
     */
    private function getRepository(string $key): ?Repositories\PackageRepository
    {
        return $this->getRepositories()->first(function (Repositories\PackageRepository $repository) use ($key) {
            return $repository::getKey() === $key;
        });
    }

    /**
     * Return the package repositories.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getRepositories(): Collection
    {
        return collect(config('app.sources'))->map(function ($source) {
            return app()->make($source);
        });
    }
}
