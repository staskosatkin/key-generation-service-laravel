<?php

namespace App\Contracts;

interface HashGenerator
{
    /**
     * @return string
     */
    public function generate(): string;
}
