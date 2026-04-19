<?php

namespace Tests\Unit;

use App\Support\RentBenchmarkMap;
use PHPUnit\Framework\TestCase;

class RentBenchmarkMapTest extends TestCase
{
    public function test_average_for_unknown_city_uses_default(): void
    {
        $this->assertSame(RentBenchmarkMap::DEFAULT_AVERAGE, RentBenchmarkMap::averageFor('Onbekende Plaats'));
    }

    public function test_average_for_null_uses_default(): void
    {
        $this->assertSame(RentBenchmarkMap::DEFAULT_AVERAGE, RentBenchmarkMap::averageFor(null));
    }

    public function test_amsterdam_above_default(): void
    {
        $this->assertGreaterThan(RentBenchmarkMap::DEFAULT_AVERAGE, RentBenchmarkMap::averageFor('Amsterdam'));
    }

    public function test_emmen_below_default(): void
    {
        $this->assertLessThan(RentBenchmarkMap::DEFAULT_AVERAGE, RentBenchmarkMap::averageFor('Emmen'));
    }

    public function test_gemeente_without_manual_override_uses_province_default(): void
    {
        $this->assertSame(900, RentBenchmarkMap::averageFor('Aa en Hunze'));
    }

    public function test_needle_to_canonical_maps_alias(): void
    {
        $map = RentBenchmarkMap::needleToCanonical();

        $this->assertSame("'s-Hertogenbosch", $map['Den Bosch']);
        $this->assertSame('Den Haag', $map['The Hague']);
    }
}
