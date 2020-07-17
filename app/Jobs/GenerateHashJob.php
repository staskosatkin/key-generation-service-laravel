<?php

namespace App\Jobs;

use App\Contracts\KeyManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateHashJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $amount;

    /**
     * Create a new job instance.
     *
     * @param int $amount
     */
    public function __construct(int $amount)
    {
        $this->amount = $amount;
    }

    /**
     * Execute the job.
     *
     * @param KeyManager $keyManager
     */
    public function handle(KeyManager $keyManager)
    {
        $start = microtime(true);
        $totalCount = $this->amount;
        [$generated, $collisions, $errors] = $keyManager->generateHashes($totalCount);
        $finish = microtime(true);
        $duration = $finish - $start;

        $speed = round($generated / $duration, 2);
        $collisionRate = $collisions ? round($generated / $collisions, 2) : '-';

        Log::debug('Bulk creating process is finished', [
            'Generated' => $generated,
            'Amount' => $this->amount,
            'Collisions' => $collisions,
            'Errors' => $errors,
            'Duration' => $duration,
            'Speed Hash/Sec' => $speed,
            'Records per 1 collision' => $collisionRate,
        ]);
    }
}
