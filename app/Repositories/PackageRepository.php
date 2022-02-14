<?php

namespace IronGate\Pkgtrends\Repositories;

use Carbon\Carbon;
use RuntimeException;

abstract class PackageRepository
{
    /**
     * The package repository key.
     */
    protected static string $key;

    /**
     * The font awesome icon for this repository.
     */
    protected static string $icon;

    /**
     * The source used for this repository.
     */
    protected static array $sources;

    /**
     * Get the package repository key.
     *
     * @return string
     */
    public static function getKey(): string
    {
        if (empty(static::$key)) {
            throw new RuntimeException('Package repository key was not set.');
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
            throw new RuntimeException('Package repository icon was not set.');
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
            throw new RuntimeException('Package repository sources was not set.');
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
