<?php

namespace Tests\Unit;

use App\Services\LocationService;
use PHPUnit\Framework\TestCase;

class LocationServiceTest extends TestCase
{
    public function test_detects_rotterdam_when_only_rotterdam_mentioned(): void
    {
        $s = new LocationService;

        $this->assertSame('Rotterdam', $s->detectCity('Te huur appartement Rotterdam centrum € 950'));
    }

    public function test_detects_amsterdam_when_only_amsterdam_mentioned(): void
    {
        $s = new LocationService;

        $this->assertSame('Amsterdam', $s->detectCity('Mooie kamer te huur in Amsterdam Oost, € 800'));
    }

    public function test_prefers_rotterdam_when_amsterdam_appears_in_nav_and_rotterdam_in_body(): void
    {
        $s = new LocationService;
        $nav = str_repeat('Huurwoningen Amsterdam Utrecht Pararius ', 5);
        $body = 'Dit betreft een woning in Rotterdam Kralingen. Huur € 1200 per maand. Geen tussenpersoon.';

        $this->assertSame('Rotterdam', $s->detectCity($nav.$body));
    }

    public function test_whole_word_does_not_match_amsterdamsestraat(): void
    {
        $s = new LocationService;

        $this->assertSame('Utrecht', $s->detectCity('Gelegen aan de Amsterdamsestraat te Utrecht, € 900'));
    }

    public function test_returns_null_when_no_known_city(): void
    {
        $s = new LocationService;

        $this->assertNull($s->detectCity('Te huur ergens in Brabant, prijs op aanvraag'));
    }
}
