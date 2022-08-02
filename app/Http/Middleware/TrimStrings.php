<?php

namespace ChiefTools\Pkgtrends\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    protected $except = [
        'password',
        'password_confirmation',
    ];
}
