<?php

namespace App\Console\Commands;

use App\Jobs\GenerateHashJob;
use App\Services\KeyManager;
use Illuminate\Bus\Dispatcher;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class GenerateHash extends Command
{
    protected $signature = 'hash:generate';

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
        $amount = (int) $this->ask('Amount', 10);

        $iterations = (int) $this->ask('Iterations', 1);
        $multiplication = 1;
        if ($iterations > 1) {
            $multiplication = $this->ask('Multiplication', 1);
        }

        Collection::times($iterations, function ($index) use ($multiplication, $amount) {
            $totalAmount = $amount * (1 + $index * ($multiplication - 1));
            $this->dispatcher->dispatch((new GenerateHashJob($totalAmount))->onQueue('generating'));
        });

        $this->info('Process is queued');
    }
}
