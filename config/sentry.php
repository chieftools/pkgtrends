<?php

return [

    'dsn' => env('APP_DEBUG', false) ? null : env('SENTRY_PRIVATE_DSN', env('SENTRY_LARAVEL_DSN')),

    'release' => env('APP_COMMIT_SHA', config('app.version')),

    'error_types' => E_ALL ^ E_DEPRECATED ^ E_USER_DEPRECATED,

    'in_app_exclude' => [
        base_path('vendor'),
        app_path('Http/Middleware'),
    ],

    'in_app_include' => [
        base_path('vendor/chieftools'),
    ],

    'send_default_pii' => true,

    'trace_propagation_targets' => [

        'pkgtrends.app',

    ],

];
