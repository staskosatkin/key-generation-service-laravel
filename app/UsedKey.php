<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UsedKey
 * @package App
 *
 * @property string $hash
 */
class UsedKey extends Model
{
    protected $table = 'used_keys';

    protected $fillable = [
        'hash'
    ];

    public $timestamps = false;
}
