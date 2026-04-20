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

    public function test_canonical_from_path_segment_matches_gemeente_slug(): void
    {
        $this->assertSame('Alkmaar', RentBenchmarkMap::canonicalFromPathSegment('alkmaar'));
        $this->assertSame('Alkmaar', RentBenchmarkMap::canonicalFromPathSegment('Alkmaar'));
        $this->assertSame("'s-Hertogenbosch", RentBenchmarkMap::canonicalFromPathSegment('den-bosch'));
        $this->assertNull(RentBenchmarkMap::canonicalFromPathSegment('huur'));
        $this->assertNull(RentBenchmarkMap::canonicalFromPathSegment('nl'));
    }

    public function test_canonical_from_path_segment_finds_city_inside_compound_slug(): void
    {
        $this->assertSame('Gooise Meren', RentBenchmarkMap::canonicalFromPathSegment('kamer-bussum'));
        $this->assertSame('Rotterdam', RentBenchmarkMap::canonicalFromPathSegment('appartement-rotterdam-centrum'));
        $this->assertSame('Utrecht', RentBenchmarkMap::canonicalFromPathSegment('huis-te-huur-utrecht'));
    }

    public function test_canonical_from_path_segment_numeric_only_returns_null(): void
    {
        $this->assertNull(RentBenchmarkMap::canonicalFromPathSegment('kamer-2371566'));
    }

    public function test_display_place_label_prefers_alias_from_url(): void
    {
        $this->assertSame(
            'Bussum',
            RentBenchmarkMap::displayPlaceLabel(
                'Gooise Meren',
                'lange html met Utrecht',
                'https://kamernet.nl/huren/kamer-bussum/veerplein/kamer-2371566'
            )
        );
    }

    public function test_display_place_label_falls_back_to_canonical_without_alias_match(): void
    {
        $this->assertSame(
            'Gooise Meren',
            RentBenchmarkMap::displayPlaceLabel('Gooise Meren', 'korte tekst zonder plaats', null)
        );
    }

    public function test_resolve_canonical_city_from_alias(): void
    {
        $this->assertSame('Gooise Meren', RentBenchmarkMap::resolveCanonicalCity('Bussum'));
        $this->assertSame('Gooise Meren', RentBenchmarkMap::resolveCanonicalCity('Gooise Meren'));
    }
}
