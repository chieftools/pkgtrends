<?php

namespace IronGate\Pkgtrends\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use IronGate\Pkgtrends\Models\Concerns\UsesUUID;
use IronGate\Pkgtrends\TrendsProvider;

/**
 * @property string                                   $id
 * @property string                                   $packages
 * @property string                                   $hash
 * @property \Carbon\Carbon                           $created_at
 * @property \Carbon\Carbon                           $updated_at
 * @property \Illuminate\Database\Eloquent\Collection $subscriptions
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

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public static function findOrCreate(string $hash, string $packages): self
    {
        return self::query()->firstOrCreate(compact('hash'), compact('hash', 'packages'));
    }
}
