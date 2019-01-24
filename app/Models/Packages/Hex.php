<?php

namespace IronGate\Pkgtrends\Models\Packages;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string name
 * @property string description
 */
class Hex extends Model
{
    public $incrementing = false;

    protected $table      = 'packages_hex';
    protected $primaryKey = 'name';
    protected $fillable   = [
        'name',
        'description',
    ];
}
