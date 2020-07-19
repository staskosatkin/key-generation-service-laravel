<?php

namespace App\Console\Commands;

use App\Jobs\GenerateHashJob;
use App\Services\KeyManager;
use Illuminate\Bus\Dispatcher;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class GenerateHash extends Command
{
    protected $signature = 'hash:generate {--amount=} {--iterations=}';

    protected $description = 'Create new Random Hash';

    private KeyManager $keyManager;

    private Dispatcher $dispatcher;

    /**
     * GenerateHash constructor.
     * @param KeyManager $keyManager
     * @param Dispatcher $dispatcher
     */
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

        Collection::times($iterations, function () use ($amount) {
            $this->dispatcher->dispatch((new GenerateHashJob($amount))->onQueue('generating'));
        });

        $this->info('Process is queued');
    }
}
