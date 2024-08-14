<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name'        => env('APP_NAME', 'Package Trends'),
    'title'       => 'Package Trends: Compare Packagist, PyPI, Hex, npm & WordPress package downloads',
    'description' => 'A quick way to compare package downloads across languages. Compare Packagist, PyPI, Hex, npm & WordPress package download statistics.',

    'version' => file_exists($versionPath = base_path('.version'))
        ? trim(file_get_contents($versionPath))
        : env('APP_VERSION', '@dev'),

    'versionString' => env('APP_VERSION_STRING', '2024.3.2'),

    /*
    |--------------------------------------------------------------------------
    | Package sources
    |--------------------------------------------------------------------------
    */

    'sources' => [
        ChiefTools\Pkgtrends\Repositories\PackagistRepository::class,
        ChiefTools\Pkgtrends\Repositories\PyPIRepository::class,
        ChiefTools\Pkgtrends\Repositories\NpmRepository::class,
        ChiefTools\Pkgtrends\Repositories\HexRepository::class,
        ChiefTools\Pkgtrends\Repositories\WordPressRepository::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Ping urls
    |--------------------------------------------------------------------------
    */

    'cron' => env('APP_CRON', true),

    'ping' => [

        'queue'               => env('PING_QUEUE'),
        'weekly'              => env('PING_WEEKLY'),
        'purge_reports'       => env('PING_PURGE_REPORTS'),
        'purge_subscriptions' => env('PING_PURGE_SUBSCRIPTIONS'),

        'import' => [

            'hex' => [

                'downloads' => env('PING_IMPORT_HEX_DOWNLOADS'),

            ],

            'pypi' => [

                'packages'  => env('PING_IMPORT_PYPI_PACKAGES'),
                'downloads' => env('PING_IMPORT_PYPI_DOWNLOADS'),

            ],

        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics
    |--------------------------------------------------------------------------
    */

    'analytics' => [

        'fathom' => [

            'site' => env('ANALYTICS_FATHOM_SITE'),

            'domain' => env('ANALYTICS_FATHOM_DOMAIN', 'piranha.assets.pkgtrends.app'),

            'public' => env('ANALYTICS_FATHOM_PUBLIC_URL'),

        ],

        'sentry' => [

            'public_dsn' => env('APP_DEBUG', false) ? null : env('SENTRY_PUBLIC_DSN', env('SENTRY_LARAVEL_DSN')),

            'public_tunnel' => env('SENTRY_PUBLIC_TUNNEL'),

            'replays' => [

                'sample_rate' => env('SENTRY_REPLAYS_SAMPLE_RATE') === null ? null : (float)env('SENTRY_REPLAYS_SAMPLE_RATE'),

                'error_sample_rate' => env('SENTRY_REPLAYS_ERROR_SAMPLE_RATE') === null ? null : (float)env('SENTRY_REPLAYS_ERROR_SAMPLE_RATE'),

            ],

        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'https://' . env('APP_DOMAIN', 'pkgtrends.app')),

    'domain' => env('APP_DOMAIN', 'pkgtrends.app'),

    'asset_url' => env('ASSET_URL'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'Europe/Amsterdam',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        // Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        // Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        // Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        ChiefTools\Pkgtrends\Providers\AppServiceProvider::class,
        ChiefTools\Pkgtrends\Providers\HorizonServiceProvider::class,
        ChiefTools\Pkgtrends\Providers\RouteServiceProvider::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => Illuminate\Support\Facades\Facade::defaultAliases()->toArray(),

];
