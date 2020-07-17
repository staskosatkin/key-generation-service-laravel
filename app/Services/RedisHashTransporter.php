<?php

namespace App\Services;

use App\Contracts\HashTransporter;
use Illuminate\Redis\RedisManager;

class RedisHashTransporter implements HashTransporter
{
    private const REDIS_KEY = 'hash-queue';

    private RedisManager $redisManager;

    public function __construct(RedisManager $redisManager)
    {
        $this->redisManager = $redisManager;
    }

    public function send(string $hash): void
    {
        $this->redisManager->client()->rPush(self::REDIS_KEY, $hash);
    }

    public function receive(): string
    {
        $start = microtime(true);
        $key = $this->redisManager->client()->lPop(self::REDIS_KEY);

        $duration = microtime(true) - $start;

        return "[$duration] $key";
    }
}
