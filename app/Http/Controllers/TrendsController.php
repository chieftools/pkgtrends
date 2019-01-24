<?php

namespace IronGate\Pkgtrends\Http\Controllers;

use Illuminate\Http\Request;
use IronGate\Pkgtrends\Repositories;
use IronGate\Pkgtrends\TrendsProvider;

class TrendsController extends Controller
{
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

        // If we could not find data for any of the dependencies (could be bogus data for example) return to the homepage
        if (!$query->hasData()) {
            return redirect()->action('TrendsController@showTrends');
        }

        return view('trends.index', compact('query', 'vendors', 'packages'));
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
