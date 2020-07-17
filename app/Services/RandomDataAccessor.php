<?php

namespace App\Services;

use App\Contracts\BigReader;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class RandomDataAccessor implements BigReader
{
    const MAX_ITERATIONS = 10;

    public function fetch(string $class, $size): Collection
    {
        $minId = $class::orderBy('id', 'asc')->offset(0)->limit(1)->first()->id;
        $maxId = $class::orderBy('id', 'desc')->offset(0)->limit(1)->first()->id;

        $records = $this->singleFetch($class, $minId, $maxId, $size);

        $iteration = 1;
        while ($records->count() < $size)
        {
            Log::warning('Missed records', [
                'Fetched' => $records->count(),
                'Needed' => $size,
                'Pid' => getmygid(),
            ]);
            $left = $size - $records->count();
            $records->merge($this->singleFetch($class, $minId, $maxId, $left));
            $iteration ++;

            if ($iteration > self::MAX_ITERATIONS) {
                Log::alert('Max iterations case occurred');
                // Handle this event properly in case of many amount need to recreate available table
                break;
            }
        }

        return $records;
    }

    private function singleFetch(string $class, int $minId, int $maxId, int $size): Collection
    {
        $randomIndex = random_int($minId, $maxId - 1 - $size);
        return $class::where('id', '>', $randomIndex)->where('id', '<=', $randomIndex + $size)
            ->orderBy('id', 'asc')->limit($size)->get();
    }
}
