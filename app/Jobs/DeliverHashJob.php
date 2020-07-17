<?php

namespace App\Jobs;

use App\Contracts\KeyManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Facades\Log;

class DeliverHashJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $count;

    /**
     * Create a new job instance.
     *
     * @param int $count
     */
    public function __construct(int $count)
    {
        $this->count = $count;
    }

    /**
     * Execute the job.
     *
     * @param KeyManager $keyManager
     * @param RedisManager $redisManager
     * @return void
     */
    public function handle(KeyManager $keyManager, RedisManager $redisManager)
    {
        $start = microtime(true);

        $keys = $keyManager->fetchHash($this->count);

        $finish = microtime(true);
        $duration = $finish - $start;

        $speed = $this->count / $duration;

        Log::debug('Fetching process was finished', [
            'Count' => $this->count,
            'Duration' => $duration,
            'Speed' => round($speed, 2),
        ]);

        collect($keys)->each(function ($hash) use ($redisManager) {
            $redisManager->client()->lPush('hash-queue', $hash);
        });

        Log::debug('Messages were published');
    }
}
