<?php

namespace App\Contracts;

interface KeyManager
{
    public function createHash(string $hash): bool;

    public function fetchHash(int $size): array;

    public function returnHash(string $hash): bool;

    public function generateHashes(int $amount): array;
}
