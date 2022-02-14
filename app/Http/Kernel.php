<?php

namespace IronGate\Pkgtrends\Http;

use Illuminate\Auth\Middleware as AuthMiddleware;
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
        LaravelMiddleware\CheckForMaintenanceMode::class,
        LaravelMiddleware\ValidatePostSize::class,
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
            SessionMiddleware\AuthenticateSession::class,
            ViewMiddleware\ShareErrorsFromSession::class,
            Middleware\VerifyCsrfToken::class,
            RoutingMiddleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:60,1',
            'bindings',
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
        'auth'          => AuthMiddleware\Authenticate::class,
        'auth.basic'    => AuthMiddleware\AuthenticateWithBasicAuth::class,
        'bindings'      => RoutingMiddleware\SubstituteBindings::class,
        'cache.headers' => HttpMiddleware\SetCacheHeaders::class,
        'can'           => AuthMiddleware\Authorize::class,
        'throttle'      => RoutingMiddleware\ThrottleRequests::class,
    ];
}
