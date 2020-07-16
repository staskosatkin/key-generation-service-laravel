<?php

namespace App\Http\Controllers;

use Illuminate\Redis\RedisManager;

class ApiController extends Controller
{
    private RedisManager $redisManager;

    public function __construct(RedisManager $redisManager)
    {
        $this->redisManager = $redisManager;
    }

    public function fetch()
    {
        return $this->redisManager->client()->lPop('hash-queue');
    }
}
