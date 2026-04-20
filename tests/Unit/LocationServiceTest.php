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

    public function test_detects_den_bosch_alias_as_canonical_key(): void
    {
        $s = new LocationService;

        $this->assertSame(
            "'s-Hertogenbosch",
            $s->detectCity('Appartement te huur Den Bosch centrum € 950 per maand')
        );
    }

    public function test_detects_groningen(): void
    {
        $s = new LocationService;

        $this->assertSame('Groningen', $s->detectCity('Kamer te huur nabij centrum Groningen, € 450'));
    }

    public function test_detects_spijkenisse_alias_as_voorne_aan_zee(): void
    {
        $s = new LocationService;

        $this->assertSame('Voorne aan Zee', $s->detectCity('Appartement te huur Spijkenisse, € 1100'));
    }

    public function test_prefers_city_from_url_path_over_footer_text(): void
    {
        $s = new LocationService;
        $footer = str_repeat('Zoek ook in Arnhem. ', 120);

        $text = 'Lange pagina met ruis. '.$footer;
        $url = 'https://voorbeeld.nl/huur/alkmaar/appartement/123';

        $this->assertSame('Alkmaar', $s->detectCity($text, $url));
    }

    public function test_prefers_alkmaar_in_title_over_arnhem_in_footer_on_long_scraped_page(): void
    {
        $s = new LocationService;
        $title = 'Te huur in Alkmaar € 850 per maand. ';
        $middle = str_repeat('Rustige straat, goed onderhouden. ', 400);
        $footer = str_repeat(' ', 5000).' Ook interessant: woningen in Arnhem en omstreken.';

        $text = $title.$middle.$footer;

        $this->assertSame('Alkmaar', $s->detectCity($text, null));
    }

    public function test_bussum_alias_and_comma_line_beats_amsterdam_in_nav(): void
    {
        $s = new LocationService;
        $nav = str_repeat('Huur Amsterdam Utrecht Pararius ', 4);
        $listing = 'Gestoffeerd Kamer te huur. Veerplein, Bussum. € 650 per maand.';

        $this->assertSame('Gooise Meren', $s->detectCity($nav.$listing, null));
    }

    public function test_comma_address_in_text_overrides_wrong_city_in_url(): void
    {
        $s = new LocationService;
        $text = 'Kamer te huur. Adres: Hoofdstraat, Bussum. € 700 p.m.';
        $url = 'https://voorbeeld.nl/huur/amsterdam/kamer/999';

        $this->assertSame('Gooise Meren', $s->detectCity($text, $url));
    }

    public function test_kamernet_style_url_prefers_city_in_slug_over_listing_text(): void
    {
        $s = new LocationService;
        $nav = str_repeat('Ook in Utrecht en Amsterdam. ', 30);
        $url = 'https://kamernet.nl/huren/kamer-bussum/veerplein/kamer-2371566';

        $this->assertSame('Gooise Meren', $s->detectCity($nav, $url));
    }

    public function test_url_slug_beats_early_nav_comma_city_even_when_veerplein_only_in_body(): void
    {
        $s = new LocationService;
        /* Nav: “, Utrecht” geeft komma-score + context; veel sites hebben zulke patronen bovenaan. */
        $nav = str_repeat('x', 35).', Utrecht € 450 huur per maand. ';
        $body = str_repeat('Lichte kamer. ', 120).'Adres: Veerplein. € 650 p.m. Geen Bussum in deze snippet.';
        $url = 'https://kamernet.nl/huren/kamer-bussum/veerplein/kamer-2371566';

        $this->assertSame('Gooise Meren', $s->detectCity($nav.$body, $url));
    }

    public function test_kamernet_slug_beats_utrecht_comma_later_in_scraped_html(): void
    {
        $s = new LocationService;
        /* Ver in de HTML: “…, Utrecht” met €/huur — zou anders URL overschrijven; hub-ruis t.o.v. kamer-bussum. */
        $prefix = str_repeat('Blok met algemene tekst. ', 120);
        $tail = 'Voorbeeld: Veerplein 12, Utrecht. € 900 p.m. te huur.';
        $url = 'https://kamernet.nl/huren/kamer-bussum/veerplein/kamer-2371566';

        $this->assertSame('Gooise Meren', $s->detectCity($prefix.$tail, $url));
    }
}
