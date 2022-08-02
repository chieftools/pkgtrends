<?php

namespace ChiefTools\Pkgtrends\Http;

use Illuminate\Http\Middleware as HttpMiddleware;
use Illuminate\View\Middleware as ViewMiddleware;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Cookie\Middleware as CookieMiddleware;
use Illuminate\Routing\Middleware as RoutingMiddleware;
use Illuminate\Session\Middleware as SessionMiddleware;
use Illuminate\Foundation\Http\Middleware as LaravelMiddleware;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        LaravelMiddleware\PreventRequestsDuringMaintenance::class,
        LaravelMiddleware\ValidatePostSize::class,
        HttpMiddleware\HandleCors::class,
        Middleware\TrimStrings::class,
        LaravelMiddleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            Middleware\EncryptCookies::class,
            CookieMiddleware\AddQueuedCookiesToResponse::class,
            SessionMiddleware\StartSession::class,
            ViewMiddleware\ShareErrorsFromSession::class,
            Middleware\VerifyCsrfToken::class,
            RoutingMiddleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:60,1',
            RoutingMiddleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'signed'   => RoutingMiddleware\ValidateSignature::class,
        'throttle' => RoutingMiddleware\ThrottleRequests::class,
    ];
}
