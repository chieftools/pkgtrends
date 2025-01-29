<?php

namespace ChiefTools\Pkgtrends\Models;

use Illuminate\Database\Eloquent\Model;
use ChiefTools\Pkgtrends\TrendsProvider;
use Stayallive\Laravel\Eloquent\UUID\UsesUUID;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string                                                                                   $id
 * @property string                                                                                   $packages
 * @property string                                                                                   $hash
 * @property string                                                                                   $permalink
 * @property \Carbon\Carbon                                                                           $created_at
 * @property \Carbon\Carbon                                                                           $updated_at
 * @property \Illuminate\Database\Eloquent\Collection<int, \ChiefTools\Pkgtrends\Models\Subscription> $subscriptions
 */
class Report extends Model
{
    use UsesUUID;

    protected $fillable = [
        'hash',
        'packages',
    ];

    public function getTrends(): TrendsProvider
    {
        return new TrendsProvider($this->packages);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\ChiefTools\Pkgtrends\Models\Subscription, $this> */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function getPermalinkAttribute(): string
    {
        return route('home', [$this->packages]);
    }

    public static function findOrCreate(string $hash, string $packages): self
    {
        return self::query()->firstOrCreate(compact('hash'), compact('hash', 'packages'));
    }
}
