<?php

namespace ChiefTools\Pkgtrends\Providers;

use Illuminate\Support\ServiceProvider;
use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->singleton(BigQueryClient::class, static function () {
            $keyFile = json_decode(config('services.google.credentials'), true);

            return new BigQueryClient([
                'projectId' => $keyFile['project_id'],
                'keyFile'   => $keyFile,
            ]);
        });
    }

    public function register(): void
    {
        // Register all the package sources as singletons
        foreach ((array)config('app.sources') as $provider) {
            $this->app->singleton($provider, static fn () => new $provider);
        }

        PreventRequestsDuringMaintenance::except([
            'statview/*',
        ]);
    }
}
