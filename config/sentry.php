<?php

return [

    'dsn' => env('APP_DEBUG', false) ? null : env('SENTRY_DSN'),

    'release' => env('APP_DEBUG', false) ? '@develop' : config('app.version'),

    'error_types' => E_ALL ^ E_DEPRECATED ^ E_USER_DEPRECATED,

    'send_default_pii' => true,

];
