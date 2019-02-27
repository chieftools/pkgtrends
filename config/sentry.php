<?php

return [

    'dsn' => env('APP_DEBUG', false) ? null : env('SENTRY_DSN'),

    'release' => env('APP_DEBUG', false) ? '@develop' : config('app.version'),

    'breadcrumbs.sql_bindings' => true,

];
