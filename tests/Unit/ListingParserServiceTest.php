<?php

namespace Tests\Unit;

use App\Services\ListingFetchService;
use App\Services\ListingParserService;
use App\Services\ListingSurfaceParser;
use PHPUnit\Framework\TestCase;

class ListingParserServiceTest extends TestCase
{
    private function makeParser(): ListingParserService
    {
        $fetch = $this->createStub(ListingFetchService::class);

        return new ListingParserService($fetch, new ListingSurfaceParser);
    }

    public function test_contact_hint_extracts_consecutive_06(): void
    {
        $p = $this->makeParser();
        $r = $p->parseInput('Bel 06-12345678 voor meer info');
        $this->assertSame('06-12345678', $r->contact);
    }

    public function test_contact_hint_extracts_grouped_06(): void
    {
        $p = $this->makeParser();
        $r = $p->parseInput('Telefoon: 06 12 34 56 78');
        $this->assertSame('06 12 34 56 78', $r->contact);
    }

    public function test_contact_hint_extracts_plus_31_mobile(): void
    {
        $p = $this->makeParser();
        $r = $p->parseInput('Bereikbaar op +31 6 12345678');
        $this->assertSame('+31 6 12345678', $r->contact);
    }

    public function test_contact_hint_extracts_plus316_compact(): void
    {
        $p = $this->makeParser();
        $r = $p->parseInput('WhatsApp +31612345678');
        $this->assertSame('+31612345678', $r->contact);
    }

    public function test_contact_hint_extracts_email_when_no_phone(): void
    {
        $p = $this->makeParser();
        $r = $p->parseInput('Mail naar jan.voorbeeld@example.com voor een afspraak');
        $this->assertSame('jan.voorbeeld@example.com', $r->contact);
    }

    public function test_contact_hint_extracts_wa_me(): void
    {
        $p = $this->makeParser();
        $r = $p->parseInput('Chat via https://wa.me/31612345678');
        $this->assertSame('WhatsApp: 31612345678', $r->contact);
    }

    public function test_contact_hint_returns_null_when_no_contact_signal(): void
    {
        $p = $this->makeParser();
        $r = $p->parseInput('Te huur kamer centrum, geen telefoon vermeld.');
        $this->assertNull($r->contact);
    }
}
