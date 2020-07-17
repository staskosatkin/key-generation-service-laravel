<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AvailableKey
 * @package App
 *
 * @property string $hash
 */
class AvailableKey extends Model
{
    protected $table = 'available_keys';

    protected $fillable = [
        'hash'
    ];

    public $timestamps = false;
}
