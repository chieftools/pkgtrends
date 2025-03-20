<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages unless another mailer is explicitly specified when sending
    | the message. All additional mailers can be configured within the
    | "mailers" array. Examples of each type of mailer are provided.
    |
    */

    'default' => env('MAIL_MAILER', 'failover'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers that can be used
    | when delivering an email. You may specify which one you're using for
    | your mailers below. You may also add additional mailers if needed.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "resend", "log", "array",
    |            "failover", "roundrobin"
    |
    */

    'mailers' => [

        'lettermint' => [
            'transport'    => 'smtp',
            'url'          => env('MAIL_LETTERMINT_URL'),
            'host'         => env('MAIL_LETTERMINT_HOST', 'smtp.lettermint.co'),
            'port'         => env('MAIL_LETTERMINT_PORT', 465),
            'encryption'   => env('MAIL_LETTERMINT_ENCRYPTION', 'tls'),
            'username'     => env('MAIL_LETTERMINT_USERNAME'),
            'password'     => env('MAIL_LETTERMINT_PASSWORD'),
            'timeout'      => 10,
            'local_domain' => env('MAIL_LETTERMINT_EHLO_DOMAIN', parse_url(env('APP_URL', 'https://pkgtrends.app'), PHP_URL_HOST)),
        ],

        'postmark' => [
            'transport'    => 'smtp',
            'url'          => env('MAIL_POSTMARK_URL'),
            'host'         => env('MAIL_POSTMARK_HOST', 'smtp.postmarkapp.com'),
            'port'         => env('MAIL_POSTMARK_PORT', 587),
            'encryption'   => env('MAIL_POSTMARK_ENCRYPTION', 'tls'),
            'username'     => env('MAIL_POSTMARK_USERNAME'),
            'password'     => env('MAIL_POSTMARK_PASSWORD'),
            'timeout'      => 10,
            'local_domain' => env('MAIL_POSTMARK_EHLO_DOMAIN', parse_url(env('APP_URL', 'https://pkgtrends.app'), PHP_URL_HOST)),
        ],

        'scaleway' => [
            'transport'    => 'smtp',
            'url'          => env('MAIL_SCALEWAY_URL'),
            'host'         => env('MAIL_SCALEWAY_HOST', 'smtp.tem.scw.cloud'),
            'port'         => env('MAIL_SCALEWAY_PORT', 465),
            'encryption'   => env('MAIL_SCALEWAY_ENCRYPTION', 'tls'),
            'username'     => env('MAIL_SCALEWAY_USERNAME'),
            'password'     => env('MAIL_SCALEWAY_PASSWORD'),
            'timeout'      => 10,
            'local_domain' => env('MAIL_SCALEWAY_EHLO_DOMAIN', parse_url(env('APP_URL', 'https://pkgtrends.app'), PHP_URL_HOST)),
        ],

        'log' => [
            'transport' => 'log',
            'channel'   => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers'   => [
                'lettermint',
                'scaleway',
                'postmark',
                'log',
            ],
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers'   => [
                'lettermint',
                'scaleway',
                'postmark',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all emails sent by your application to be sent from
    | the same address. Here you may specify a name and address that is
    | used globally for all emails that are sent by your application.
    |
    */

    'from' => [
        'name'    => env('MAIL_FROM_NAME', 'Package Trends'),
        'address' => env('MAIL_FROM_ADDRESS', 'reports@pkgtrends.app'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Markdown Mail Settings
    |--------------------------------------------------------------------------
    |
    | If you are using Markdown based email rendering, you may configure your
    | theme and component paths here, allowing you to customize the design
    | of the emails. Or, you may simply stick with the Laravel defaults!
    |
    */

    'markdown' => [
        'theme' => env('MAIL_MARKDOWN_THEME', 'default'),

        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],

];
