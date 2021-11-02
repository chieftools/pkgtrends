<?php

namespace IronGate\Pkgtrends\Providers;

use Illuminate\Support\ServiceProvider;
use Google\Cloud\BigQuery\BigQueryClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->ensureMixAndAssetUrlsAreConfigured();

        $this->app->singleton(BigQueryClient::class, function () {
            $keyFile = json_decode(config('services.google.credentials'), true);

            return new BigQueryClient([
                'projectId'   => 'package-trends',
                'keyFile'     => $keyFile,
                'keyFilePath' => storage_path('creds/google-bigquery.json'),
            ]);
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register all the package sources as singletons
        foreach ((array)config('app.sources') as $provider) {
            $this->app->singleton($provider, function () use ($provider) {
                return new $provider;
            });
        }
    }

    private function ensureMixAndAssetUrlsAreConfigured(): void
    {
        if (!isset($_SERVER['VAPOR_ARTIFACT_NAME'])) {
            return;
        }

        config([
            'app.mix_url'   => $this->replaceCustomAssetDomain($_ENV['MIX_URL'] ?? '/'),
            'app.asset_url' => $this->replaceCustomAssetDomain($_ENV['ASSET_URL'] ?? '/'),
        ]);
    }

    /**
     * Replace the Vapor asset domain with a custom asset domain.
     *
     * @param string|null $assetUrl
     *
     * @return string|null
     *
     * @noinspection LaravelFunctionsInspection
     */
    private function replaceCustomAssetDomain(?string $assetUrl): ?string
    {
        if ($assetUrl === null) {
            return $assetUrl;
        }

        $plainDomain  = env('VAPOR_ASSET_DOMAIN');
        $customDomain = env('VAPOR_CUSTOM_ASSET_DOMAIN');

        if ($plainDomain === null || $customDomain === null) {
            return $assetUrl;
        }

        return str_replace($plainDomain, $customDomain, $assetUrl);
    }
}
