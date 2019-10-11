<?php

namespace IronGate\Pkgtrends\Repositories;

use Carbon\Carbon;
use GuzzleHttp\Client;

abstract class PackageRepository
{
    /**
     * The package repository key.
     *
     * @var string
     */
    protected static $key;

    /**
     * The font awesome icon for this repository.
     *
     * @var string
     */
    protected static $icon;

    /**
     * The source used for this repository.
     *
     * @var array
     */
    protected static $sources;

    /**
     * The base uri used for the HTTP client.
     *
     * @var string
     */
    protected $baseUri;

    /**
     * A Guzzle HTTP client instance.
     *
     * @var \GuzzleHttp\Client
     */
    protected $http;

    /**
     * PackageRepository constructor.
     */
    public function __construct()
    {
        $this->http = new Client(['base_uri' => $this->baseUri]);
    }

    /**
     * Get the package repository key.
     *
     * @return string
     */
    public static function getKey(): string
    {
        if (empty(static::$key)) {
            throw new \RuntimeException('Package repository key was not set.');
        }

        return static::$key;
    }

    /**
     * Get the package repository icon.
     *
     * @return string
     */
    public static function getIcon(): string
    {
        if (empty(static::$icon)) {
            throw new \RuntimeException('Package repository icon was not set.');
        }

        return static::$icon;
    }

    /**
     * Get the package repository sources.
     *
     * @return array
     */
    public static function getSources(): array
    {
        if (empty(static::$sources)) {
            throw new \RuntimeException('Package repository sources was not set.');
        }

        return static::$sources;
    }

    /**
     * Search for a package using a query.
     *
     * @param string $query
     *
     * @return array
     */
    abstract public function searchPackage(string $query): array;

    /**
     * Get the package info using an exact package name.
     *
     * @param string $name
     *
     * @return array|null
     */
    abstract public function getPackage(string $name): ?array;

    /**
     * Retrieve the package stats for a exact package name.
     *
     * @param string         $name
     * @param \Carbon\Carbon $start
     * @param \Carbon\Carbon $end
     *
     * @return array|null
     */
    abstract public function getPackageStats(string $name, Carbon $start, Carbon $end): ?array;
}
