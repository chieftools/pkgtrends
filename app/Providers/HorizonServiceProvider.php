<?php

namespace IronGate\Pkgtrends\Providers;

use Laravel\Horizon\Horizon;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        Horizon::night();
    }

    protected function gate(): void
    {
        Gate::define('viewHorizon', static function ($user = null): bool {
            if ($user === null) {
                return request()->bearerToken() === config('services.horizon.secret');
            }

            return false;
        });
    }
}
