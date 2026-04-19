<?php

namespace App\Services;

use App\Support\RentBenchmarkMap;

class PriceAnalysisService
{
    /**
     * @return array{average: int, difference_percent: ?int}
     */
    public function analyze(?string $city, ?int $price): array
    {
        $avg = RentBenchmarkMap::averageFor($city);

        return [
            'average' => $avg,
            'difference_percent' => $price
                ? (int) round((($price - $avg) / $avg) * 100)
                : null,
        ];
    }
}
