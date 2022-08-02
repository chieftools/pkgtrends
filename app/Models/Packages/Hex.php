<?php

namespace ChiefTools\Pkgtrends\Models\Packages;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string         $name
 * @property string|null    $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Hex extends Model
{
    public $incrementing = false;

    protected $table      = 'packages_hex';
    protected $keyType    = 'string';
    protected $primaryKey = 'name';
    protected $fillable   = [
        'name',
        'description',
    ];
}
