<?php

namespace IronGate\Pkgtrends\Packages;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string project
 * @property string description
 */
class PyPI extends Model
{
    public $incrementing    = false;
    protected $primaryKey   = 'project';
    protected $table        = 'packages_pypi';
    protected $fillable     = [
        'project',
        'description',
    ];
}
