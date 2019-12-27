<?php

namespace IronGate\Pkgtrends\Models\Packages;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string         $project
 * @property string|null    $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class PyPI extends Model
{
    public $incrementing = false;

    protected $table = 'packages_pypi';
    protected $keyType = 'string';
    protected $primaryKey = 'project';
    protected $fillable = [
        'project',
        'description',
    ];
}
