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
            $credentials = config('services.google.credentials');

            if (str_starts_with($credentials, 'base64:')) {
                $credentials = base64_decode(substr($credentials, 7));
            }

            $keyFile = json_decode($credentials, true);

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
