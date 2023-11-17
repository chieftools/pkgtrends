<?php

namespace ChiefTools\Pkgtrends\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laracasts\Utilities\JavaScript\JavaScriptFacade as JavaScript;

class JavaScriptInjector
{
    public function handle(Request $request, Closure $next): mixed
    {
        $sentryDsn = config('app.analytics.sentry.public_dsn');

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        JavaScript::put([
            'ENV'            => app()->environment(),
            'CSRF'           => csrf_token(),
            'BASE'           => config('app.domain'),
            'HOME'           => url()->to('/'),
            'DEBUG'          => config('app.debug'),
            'SENTRY'         => [
                'DSN'                       => $sentryDsn,
                'TUNNEL'                    => config('app.analytics.sentry.public_tunnel'),
                'RELEASE'                   => config('sentry.release'),
                'TRACES_SAMPLE_RATE'        => $sentryDsn !== null ? config('sentry.traces_sample_rate', 0) : 0,
                'REPLAYS_SAMPLE_RATE'       => $sentryDsn !== null ? config('app.analytics.sentry.replays.sample_rate') : 0,
                'REPLAYS_ERROR_SAMPLE_RATE' => $sentryDsn !== null ? config('app.analytics.sentry.replays.error_sample_rate') : 0,
            ],
            'VERSION'        => config('app.version'),
            'VERSION_STRING' => config('app.versionString') . ' (' . config('app.version') . ')',
        ]);

        return $next($request);
    }
}
