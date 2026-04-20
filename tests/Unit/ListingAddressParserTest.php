<?php

namespace Tests\Unit;

use App\Services\ListingAddressParser;
use PHPUnit\Framework\TestCase;

class ListingAddressParserTest extends TestCase
{
    public function test_finds_street_with_suffix(): void
    {
        $p = new ListingAddressParser;
        $text = "Te huur aan de Kerkstraat 42 in het centrum.\nHuur € 950 per maand.";

        $r = $p->parseStreetAndNumber($text);

        $this->assertStringContainsString('Kerkstraat', $r['street'] ?? '');
        $this->assertSame('42', $r['number'] ?? null);
    }

    public function test_returns_nulls_when_no_address(): void
    {
        $p = new ListingAddressParser;

        $r = $p->parseStreetAndNumber('Te huur ergens € 500 per maand. Alleen WhatsApp.');

        $this->assertNull($r['street']);
        $this->assertNull($r['number']);
    }

    public function test_ignores_ui_menu_lines_and_finds_real_street_later(): void
    {
        $p = new ListingAddressParser;
        $text = <<<'TXT'
Aan / Add to favorite
Deel dit op Facebook
Te huur: appartement in het centrum.
Adres: Hoofdstraat 88 (bezichtiging op afspraak).
Huur € 1100 per maand.
TXT;

        $r = $p->parseStreetAndNumber($text);

        $this->assertStringContainsString('Hoofdstraat', $r['street'] ?? '');
        $this->assertSame('88', $r['number'] ?? null);
    }

    public function test_does_not_use_add_to_favorite_as_street(): void
    {
        $p = new ListingAddressParser;

        $r = $p->parseStreetAndNumber("Aan / Add to favorite\nMeer info bel 06-12345678.");

        $this->assertNull($r['street']);
        $this->assertNull($r['number']);
    }

    public function test_derives_street_from_directwonen_url_when_text_has_no_street_line(): void
    {
        $p = new ListingAddressParser;
        $url = 'https://directwonen.nl/huurwoningen-huren/alkmaar/coornhertkade/appartement-512619';
        $text = 'Korte omschrijving zonder straatregel. Huur € 900 per maand.';

        $r = $p->parseStreetAndNumber($text, $url);

        $this->assertSame('Coornhertkade', $r['street']);
        $this->assertNull($r['number']);
    }

    public function test_text_street_wins_over_url(): void
    {
        $p = new ListingAddressParser;
        $url = 'https://directwonen.nl/huurwoningen-huren/alkmaar/coornhertkade/appartement-512619';
        $text = "Te huur aan de Hoofdstraat 12 in het centrum.\nHuur € 950 per maand.";

        $r = $p->parseStreetAndNumber($text, $url);

        $this->assertStringContainsString('Hoofdstraat', $r['street'] ?? '');
        $this->assertSame('12', $r['number'] ?? null);
    }
}
