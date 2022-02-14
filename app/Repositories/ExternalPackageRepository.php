<?php

namespace IronGate\Pkgtrends\Repositories;

use GuzzleHttp\Client;

abstract class ExternalPackageRepository extends PackageRepository
{
    /**
     * The base uri used for the HTTP client.
     */
    protected string $baseUri;

    /**
     * A Guzzle HTTP client instance.
     */
    protected Client $http;

    public function __construct()
    {
        $this->http = http($this->baseUri);
    }
}
