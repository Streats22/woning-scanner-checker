<?php

namespace Tests\Unit;

use App\Services\PriceAnalysisService;
use App\Support\RentBenchmarkMap;
use PHPUnit\Framework\TestCase;

class PriceAnalysisServiceTest extends TestCase
{
    public function test_difference_percent_against_city_benchmark(): void
    {
        $s = new PriceAnalysisService();

        $out = $s->analyze('Amsterdam', 1000);

        $this->assertSame(RentBenchmarkMap::averageFor('Amsterdam'), $out['average']);
        $this->assertNotNull($out['difference_percent']);
        $this->assertLessThan(0, $out['difference_percent']);
    }

    public function test_null_price_yields_null_difference(): void
    {
        $s = new PriceAnalysisService();

        $out = $s->analyze('Rotterdam', null);

        $this->assertNull($out['difference_percent']);
    }
}
