<?php

namespace IronGate\Pkgtrends\Stats;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string date
 * @property string projects
 * @property int    downloads
 */
class PyPI extends Model
{
    protected $table      = 'stats_pypi';
    public $timestamps    = false;
    protected $fillable   = [
        'date',
        'project',
        'downloads',
    ];
}
