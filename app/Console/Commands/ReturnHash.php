<?php

namespace App\Console\Commands;

use App\Contracts\KeyManager;
use Illuminate\Console\Command;

class ReturnHash extends Command
{
    protected $signature = 'hash:return';

    protected $description = 'Return Old Hash';

    private KeyManager $keyManager;

    public function __construct(KeyManager $keyManager)
    {
        parent::__construct();
        $this->keyManager = $keyManager;
    }

    public function handle()
    {
        $hash = $this->ask("Hash: ");

        $this->keyManager->returnHash($hash);

        $this->info('Hash was returned');
    }
}
