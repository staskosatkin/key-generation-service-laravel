<?php

namespace App\Console\Commands;

use App\Contracts\KeyManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CreateHash extends Command
{
    protected $signature = 'hash:create';

    protected $description = 'Create new Available Hash';

    private KeyManager $keyManager;

    /**
     * CreateHash constructor.
     * @param KeyManager $keyManager
     */
    public function __construct(KeyManager $keyManager)
    {
        parent::__construct();
        $this->keyManager = $keyManager;
    }

    public function handle()
    {
        $hash = $this->ask('Enter Hash');

        if ($this->keyManager->createHash($hash)) {
            Log::debug("Hash `$hash` wash created");
        } else {
            Log::error("Couldn't create hash $hash");
        }
    }
}
