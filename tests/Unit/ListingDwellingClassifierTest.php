<?php

namespace Tests\Unit;

use App\Data\ParsedListingInput;
use App\Services\ListingDwellingClassifier;
use Tests\TestCase;

class ListingDwellingClassifierTest extends TestCase
{
    public function test_studentenkamer_is_room(): void
    {
        $c = new ListingDwellingClassifier;
        $d = new ParsedListingInput(null, 450, null, 'Lichte studentenkamer te huur in Utrecht, €450 p.m. Huisgenoten.');
        $out = $c->classify($d);

        $this->assertSame('room', $out['kind']);
        $this->assertContains('kamer', $out['signals']);
    }

    public function test_room_defaults_to_private_sector_when_no_explicit_signals(): void
    {
        $c = new ListingDwellingClassifier;
        $d = new ParsedListingInput(null, 450, null, 'Lichte studentenkamer te huur in Utrecht, €450 p.m. Huisgenoten.');
        $out = $c->classify($d);

        $this->assertSame('private', $out['rental_sector']);
        $this->assertSame('medium', $out['sector_confidence']);
        $this->assertContains('kamer-particulier', $out['signals']);
    }

    public function test_room_does_not_override_explicit_sociale_huur(): void
    {
        $c = new ListingDwellingClassifier;
        $d = new ParsedListingInput(null, 400, null, 'Kamer te huur via woningcorporatie, sociale huur, inschrijving vereist.');
        $out = $c->classify($d);

        $this->assertSame('room', $out['kind']);
        $this->assertSame('social', $out['rental_sector']);
    }

    public function test_appartement_is_whole(): void
    {
        $c = new ListingDwellingClassifier;
        $d = new ParsedListingInput(null, 1200, null, 'Modern appartement te huur Amsterdam, 3 slaapkamers, €1200 per maand.');
        $out = $c->classify($d);

        $this->assertSame('whole', $out['kind']);
    }

    public function test_sociale_huur_signals(): void
    {
        $c = new ListingDwellingClassifier;
        $d = new ParsedListingInput(null, 520, null, 'Woning via woningcorporatie, sociale huur, toewijzing volgens inschrijving.');
        $out = $c->classify($d);

        $this->assertSame('social', $out['rental_sector']);
    }

    public function test_particulier_signals(): void
    {
        $c = new ListingDwellingClassifier;
        $d = new ParsedListingInput(null, 950, null, 'Te huur door particuliere verhuurder, vrije sector appartement.');
        $out = $c->classify($d);

        $this->assertSame('private', $out['rental_sector']);
        $this->assertContains('particulier', $out['signals']);
    }
}
