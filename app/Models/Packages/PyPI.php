<?php

namespace IronGate\Pkgtrends\Models\Packages;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string project
 * @property string description
 */
class PyPI extends Model
{
    public $incrementing = false;

    protected $table      = 'packages_pypi';
    protected $primaryKey = 'project';
    protected $fillable   = [
        'project',
        'description',
    ];
}
