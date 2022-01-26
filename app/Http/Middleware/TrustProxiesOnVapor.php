<?php

namespace IronGate\Pkgtrends\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Middleware\TrustProxies;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class TrustProxiesOnVapor extends TrustProxies
{
    protected function setTrustedProxyIpAddresses(Request $request): void
    {
        if ($this->is_running_on_vapor()) {
            $this->proxies = ['0.0.0.0/0', '2000:0:0:0:0:0:0:0/3'];
            $this->headers = SymfonyRequest::HEADER_X_FORWARDED_FOR;
        }

        parent::setTrustedProxyIpAddresses($request);
    }

    private function is_running_on_vapor(): bool
    {
        return isset($_SERVER['VAPOR_ARTIFACT_NAME']);
    }
}
