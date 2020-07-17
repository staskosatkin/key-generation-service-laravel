<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface BigReader
{
    public function fetch(string $class, $size): Collection;
}
