<?php

namespace IronGate\Pkgtrends\Models\Stats;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $date
 * @property string $project
 * @property int    $downloads
 */
class PyPI extends Model
{
    public $timestamps = false;

    protected $table = 'stats_pypi';
    protected $fillable = [
        'date',
        'project',
        'downloads',
    ];
}
