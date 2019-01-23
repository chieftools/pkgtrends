<?php

namespace IronGate\Pkgtrends\Models;

use IronGate\Pkgtrends\TrendsProvider;
use Illuminate\Database\Eloquent\Model;
use IronGate\Pkgtrends\Models\Traits\UsesUUID;

class Report extends Model
{
    use UsesUUID;

    public $incrementing = false;

    protected $fillable = [
        'hash',
        'packages',
    ];

    public function getTrends(): TrendsProvider
    {
        return new TrendsProvider($this->packages);
    }

    public function subscribers()
    {
        return $this->hasMany(Subscriber::class);
    }

    public static function findOrCreate(string $hash, string $packages): self
    {
        $report = self::query()->where('hash', '=', $hash)->first();

        if ($report === null) {
            $report = new self(compact('hash', 'packages'));
            $report->save();
        }

        return $report;
    }
}
