<?php

namespace App\Services;

class PriceAnalysisService
{
    /**
     * @return array{average: int, difference_percent: ?int}
     */
    public function analyze(?string $city, ?int $price): array
    {
        $map = [
            'Amsterdam' => 1800,
            'Rotterdam' => 1400,
            'Utrecht' => 1600,
            'Alkmaar' => 1200,
        ];

        $avg = $map[$city] ?? 1300;

        return [
            'average' => $avg,
            'difference_percent' => $price
                ? (int) round((($price - $avg) / $avg) * 100)
                : null,
        ];
    }
}
