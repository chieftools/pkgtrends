<?php

namespace ChiefTools\Pkgtrends\Exceptions;

use Throwable;
use Sentry\Laravel\Integration;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            Integration::captureUnhandledException($e);
        });
    }
}
