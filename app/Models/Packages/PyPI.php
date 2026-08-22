<?php

namespace ChiefTools\Pkgtrends\Models\Packages;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int            $id
 * @property string         $project
 * @property string|null    $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class PyPI extends Model
{
    protected $table    = 'packages_pypi';
    protected $fillable = [
        'project',
        'description',
    ];
}
