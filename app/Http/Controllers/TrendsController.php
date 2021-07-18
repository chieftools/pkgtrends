<?php

namespace IronGate\Pkgtrends\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use IronGate\Pkgtrends\Repositories;
use Illuminate\Http\RedirectResponse;
use IronGate\Pkgtrends\TrendsProvider;

class TrendsController extends Controller
{
    /**
     * Show the homepage with the package query data if there was a packages query string given.
     *
     * @param string|null $packagesQuery
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function showTrends(?string $packagesQuery = null): RedirectResponse|View
    {
        // Build a list of all vendors and their icons
        $vendors = collect(config('app.sources'))->mapWithKeys(
            fn ($source) => [$source::getKey() => $source::getIcon()]
        );

        // If there is no query for packages show an empty index
        if (empty($packagesQuery)) {
            return view('trends.index', compact('vendors'));
        }

        // Create the trends provider that can query the package repositories
        $query = new TrendsProvider($packagesQuery);

        // If we could not find data for any of the dependencies (could be bogus data for example) return to the homepage
        if (!$query->hasData()) {
            return redirect()->action('TrendsController@showTrends');
        }

        return view('trends.index', compact('vendors', 'query'));
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

        $querySlug = str_slug($query);

        return TrendsProvider::getRepositories()->flatMap(
            fn (Repositories\PackageRepository $repository) => cache()->remember(
                "{$repository::getKey()}:query:{$querySlug}",
                now()->addHour(),
                fn () => $repository->searchPackage($query)
            )
        )->values()->all();
    }
}
