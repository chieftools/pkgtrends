<?php

namespace ChiefTools\Pkgtrends\Models\Stats;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $date
 * @property int    $package_id
 * @property int    $downloads
 */
class PyPI extends Model
{
    public $timestamps = false;

    protected $table    = 'stats_pypi';
    protected $fillable = [
        'date',
        'package_id',
        'downloads',
    ];
}
