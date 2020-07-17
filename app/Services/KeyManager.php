<?php

namespace App\Services;

use App\AvailableKey;
use App\Contracts\BigReader;
use App\Contracts\HashGenerator;
use App\Contracts\KeyManager as KeysManagerInterface;
use App\UsedKey;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDOException;
use Throwable;

class KeyManager implements KeysManagerInterface
{
    const BATCH_SIZE = 100;

    private HashGenerator $hashGenerator;

    private BigReader $bigReader;

    /**
     * KeyManager constructor.
     * @param HashGenerator $hashGenerator
     * @param BigReader $bigReader
     */
    public function __construct(HashGenerator $hashGenerator, BigReader $bigReader)
    {
        $this->hashGenerator = $hashGenerator;
        $this->bigReader = $bigReader;
    }

    public function createHash(string $hash): bool
    {
        UsedKey::where('hash', $hash)->firstOrFail();

        factory(AvailableKey::class)->make([
            'hash' => $hash,
        ])->saveOrFail();

        return true;
    }

    /**
     * @param $size
     * @return array []string
     * @throws Throwable
     */
    public function fetchHash($size): array
    {

        DB::beginTransaction();

        try {

            $availableKeys = $this->bigReader->fetch(AvailableKey::class, $size);

            /** @var AvailableKey $ak */
            $result = $availableKeys->map(fn ($ak) => $ak->hash);

            /** @var AvailableKey $ak */
            AvailableKey::destroy($availableKeys->map(fn ($ak) => $ak->id));

            UsedKey::insert($result->map(fn ($hash) => ['hash' => $hash])->toArray());

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return $result->toArray();
    }

    /**
     * @param string $hash
     * @return bool
     * @throws Throwable
     */
    public function returnHash(string $hash): bool
    {
        DB::beginTransaction();
        try {
            /** @var UsedKey $usedKey */
            $usedKey = UsedKey::where('hash', $hash)->firstOrFail();

            $hash = $usedKey->hash;
            $usedKey->delete();

            $availableKey = new AvailableKey([
                'hash' => $hash,
            ]);
            $availableKey->saveOrFail();
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return true;
    }

    /**
     * @param int $amount
     * @return array
     */
    public function generateHashes(int $amount): array
    {
        $left = $amount;

        $created = 0;
        $collisions = 0;
        $errors = 0;

        do {
            $times = min(self::BATCH_SIZE, $left);

            if ($times > 0) {
                $hashes = Collection::times($times, function ($index) {
                    return $this->hashGenerator->generate();
                })->unique()->values();

                if ($hashes->isNotEmpty()) {
                    /** @var Collection $usedKeys */
                    $usedKeys = UsedKey::whereIn('hash', $hashes)->get()->map(fn ($uk) => $uk->hash);
                    $collisions += $usedKeys->count();

                    if ($usedKeys->isNotEmpty()) {
                        $hashes = $hashes->filter(fn (string $hash) => !in_array($hash, $usedKeys->toArray()));
                    }
                }

                if ($hashes->isNotEmpty()) {
                    /** @var Collection $availableKeys */
                    $availableKeys = AvailableKey::whereIn('hash', $hashes)->get()->map(fn ($ak) => $ak->hash);
                    $collisions += $availableKeys->count();

                    if ($availableKeys->isNotEmpty()) {
                        $hashes = $hashes->filter(fn (string $hash) => !in_array($hash, $availableKeys->toArray()));
                    }
                }

                if ($hashes->isNotEmpty()) {
                    try {
                        AvailableKey::insert($hashes->map(fn ($hash) => ['hash' => $hash])->toArray());
                    } catch (PDOException $exception) {
                        Log::error('Cannot insert hashes', ['exception' => $exception]);
                        $left -= $times;
                        $errors += 1;
                        continue;
                    }
                }

                $success = $hashes->count();
                $created += $hashes->count();

            } else {
                break;
            }

            $left -= $success;

        } while($left > 0);

        return [$created, $collisions, $errors];
    }
}
