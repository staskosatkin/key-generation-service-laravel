<?php

namespace App\Services;

use App\Contracts\HashGenerator;
use Illuminate\Support\Str;

class RandomHashGenerator implements HashGenerator
{
    const HASH_LENGTH = 6;

    public function generate(): string
    {
        return Str::random(self::HASH_LENGTH);
    }
}
