<?php

namespace App\Console;

use App\Console\Commands\CreateHash;
use App\Console\Commands\DeliverHash;
use App\Console\Commands\FetchHash;
use App\Console\Commands\GenerateHash;
use App\Console\Commands\ReturnHash;
use App\Jobs\CheckAvailableAmountJob;
use App\Jobs\GenerateHashJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        CreateHash::class,
        FetchHash::class,
        ReturnHash::class,
        GenerateHash::class,
        DeliverHash::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
         $schedule->call(function () {
             Log::debug("Run schedule task every minute");
         })->everyMinute();

         $schedule->call(function () {
             /** @var RedisManager $redis */
             $redis = app(RedisManager::class);

             if ($redis->client()->lLen('hash-queue') < 120000) {
                 Log::debug("Run hash deliver");
                 Artisan::call('hash:deliver', [
                     '--amount' => 100,
                     '--iterations' => 120,
                 ]);
             }
         })->everyMinute();

         $schedule->call(function () {
             Log::debug("Dispatch new generation Job");
             dispatch(new CheckAvailableAmountJob(500000));
         })->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
