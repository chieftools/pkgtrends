<?php

namespace IronGate\Pkgtrends\Models\Stats;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string date
 * @property string package
 * @property int    downloads
 */
class Hex extends Model
{
    public $timestamps = false;

    protected $table    = 'stats_hex';
    protected $fillable = [
        'date',
        'package',
        'downloads',
    ];
}
