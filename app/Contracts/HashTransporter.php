<?php

namespace App\Contracts;

interface HashTransporter
{
    /**
     * @param string $hash
     */
    public function send(string $hash): void;

    /**
     *
     */
    public function receive(): string;
}
