<?php

namespace App\Console\Commands;

use App\Contracts\KeyManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchHash extends Command
{
    protected $signature = 'hash:fetch {--amount=}';

    protected $description = 'Fetch New Hash';

    private KeyManager $keyManager;

    public function __construct(KeyManager $keyManager)
    {
        parent::__construct();
        $this->keyManager = $keyManager;
    }

    public function handle()
    {
        $amout = $this->option('amount');
        if (!$amout) {
            $amout = $this->ask('Bulk size', 1);
        }

        $hashes = $this->keyManager->fetchHash($amout);

        collect($hashes)->each(fn ($hash) => $this->info($hash));
    }
}
