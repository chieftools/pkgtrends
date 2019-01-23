<?php

namespace IronGate\Pkgtrends\Http\Controllers;

use Illuminate\Http\Request;
use IronGate\Pkgtrends\Repositories;
use IronGate\Pkgtrends\TrendsProvider;
use IronGate\Pkgtrends\Mail\WeeklyReport;

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
        // Build a list of all vendors and their icons
        $vendors = collect(config('app.sources'))->mapWithKeys(function ($source) {
            return [$source::getKey() => $source::getIcon()];
        });

        // If there is no query for packages show an empty view
        if (empty($packages)) {
            return view('trends.index', compact('vendors'));
        }

        // Build the trends query
        $query = new TrendsProvider($packages);

        // Retrieve the dependency data
        $dependencies = $query->getTrendsData();

        // If we could not find data for any of the dependencies (could be bogus data for example) return to the homepage
        if ($dependencies->isEmpty()) {
            return redirect()->action('TrendsController@showTrends');
        }

        // Build a "nice" page title and the graph labels
        $title      = $query->getFormattedTitle();
        $statLabels = $query->getGraphLabels();

        return view('trends.index', compact('title', 'dependencies', 'statLabels', 'vendors', 'packages'));
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

        return TrendsProvider::getRepositories()->flatMap(function (Repositories\PackageRepository $repository) use ($query) {
            return cache()->remember("{$repository::getKey()}:query:" . str_slug($query), 60, function () use ($repository, $query) {
                return $repository->searchPackage($query);
            });
        })->values()->all();
    }
}
