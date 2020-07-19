<?php

namespace App\Console\Commands;

use App\Jobs\DeliverHashJob;
use App\Services\KeyManager;
use Illuminate\Bus\Dispatcher;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class DeliverHash extends Command
{
    protected $signature = 'hash:deliver {--amount=} {--iterations=}';

    protected $description = 'Deliver stream of hashes';

    private KeyManager $keyManager;

    private Dispatcher $dispatcher;

    public function __construct(KeyManager $keyManager, Dispatcher $dispatcher)
    {
        parent::__construct();
        $this->keyManager = $keyManager;
        $this->dispatcher = $dispatcher;
    }

    public function handle()
    {
        $amount = (int) $this->option('amount');
        if (!$amount) {
            $amount = (int) $this->ask('Amount', 100);
        }

        $iterations = (int) $this->option('iterations');
        if (!$iterations) {
            $iterations = (int) $this->ask('Iterations', 1);
        }

        // dispatch Job
        Collection::times($iterations, function () use ($amount) {
            $this->dispatcher->dispatch((new DeliverHashJob($amount))->onQueue('fetching'));
        });


        $this->info('Job queued');
    }
}
