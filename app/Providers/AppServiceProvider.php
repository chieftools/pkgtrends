<?php

namespace IronGate\Pkgtrends\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        // Register all the package sources as singletons
        foreach ((array)config('app.sources') as $provider) {
            $this->app->singleton($provider, function () use ($provider) {
                return new $provider;
            });
        }
    }
}
