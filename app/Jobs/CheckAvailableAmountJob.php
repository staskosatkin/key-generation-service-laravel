<?php

namespace App\Jobs;

use App\AvailableKey;
use Illuminate\Bus\Dispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckAvailableAmountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $needed;

    /**
     * Create a new job instance.
     *
     * @param int $needed
     */
    public function __construct(int $needed)
    {
        $this->needed = $needed;
    }

    /**
     * Execute the job.
     *
     * @param Dispatcher $dispatcher
     * @return void
     */
    public function handle(Dispatcher $dispatcher)
    {
        $count = AvailableKey::count();

        if ($this->needed > $count) {
            Log::debug("Needed: {$this->needed}. Amount: {$count}. Start to generate process");
            $dispatcher->dispatch(new GenerateHashJob($this->needed));
        }
    }
}
