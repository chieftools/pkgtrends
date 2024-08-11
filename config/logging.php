<?php

$logLevel = env('LOG_LEVEL', env('APP_ENV', 'production') === 'local' ? 'debug' : 'info');

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that is utilized to write
    | messages to your logs. The value provided here should match one of
    | the channels present in the list of "channels" configured below.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    'scheduled_commands_file' => storage_path('logs/pkgtrends-schedule.log'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace'   => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Laravel
    | utilizes the Monolog PHP logging library, which includes a variety
    | of powerful log handlers and formatters that you're free to use.
    |
    | Available drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog", "custom", "stack"
    |
    */

    'channels' => [

        'stack' => [
            'driver'            => 'stack',
            'channels'          => explode(',', env('LOG_STACK', 'single')),
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver'               => 'single',
            'path'                 => storage_path('logs/pkgtrends-laravel.log'),
            'level'                => $logLevel,
            'replace_placeholders' => true,
            ...env('APP_ENV', 'production') !== 'local' ? [
                'formatter'      => Monolog\Formatter\JsonFormatter::class,
                'formatter_with' => [
                    'includeStacktraces' => true,
                ],
            ] : [],
        ],

        'daily' => [
            'driver'               => 'daily',
            'path'                 => storage_path('logs/pkgtrends-laravel.log'),
            'level'                => $logLevel,
            'days'                 => env('LOG_DAILY_DAYS', 7),
            'replace_placeholders' => true,
            ...env('APP_ENV', 'production') !== 'local' ? [
                'formatter'      => Monolog\Formatter\JsonFormatter::class,
                'formatter_with' => [
                    'includeStacktraces' => true,
                ],
            ] : [],
        ],

        'stderr' => [
            'driver'     => 'monolog',
            'level'      => $logLevel,
            'handler'    => Monolog\Handler\StreamHandler::class,
            'formatter'  => env('LOG_STDERR_FORMATTER'),
            'with'       => [
                'stream' => 'php://stderr',
            ],
            'processors' => [Monolog\Processor\PsrLogMessageProcessor::class],
        ],

        'syslog' => [
            'driver'               => 'syslog',
            'level'                => $logLevel,
            'facility'             => env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver'               => 'errorlog',
            'level'                => $logLevel,
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver'  => 'monolog',
            'handler' => Monolog\Handler\NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/pkgtrends-laravel.log'),
        ],

    ],

];
