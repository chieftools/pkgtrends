<?php

namespace IronGate\Pkgtrends\Providers;

use Illuminate\Support\ServiceProvider;
use Google\Cloud\BigQuery\BigQueryClient;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->singleton(BigQueryClient::class, static function () {
            $keyFile = json_decode(config('services.google.credentials'), true);

            return new BigQueryClient([
                'projectId'   => 'package-trends',
                'keyFile'     => $keyFile,
                'keyFilePath' => storage_path('creds/google-bigquery.json'),
            ]);
        });
    }

    public function register(): void
    {
        // Register all the package sources as singletons
        foreach ((array)config('app.sources') as $provider) {
            $this->app->singleton($provider, static fn () => new $provider);
        }
    }
}
