<?php

namespace Tests\Unit;

use App\Data\ParsedListingInput;
use App\Services\ListingContentFitService;
use Tests\TestCase;

class ListingContentFitServiceTest extends TestCase
{
    public function test_typical_rental_text_scores_strong(): void
    {
        $service = new ListingContentFitService();
        $text = <<<'TXT'
        Te huur: lichte kamer in Amsterdam centrum, 12 m², € 650 per maand inclusief.
        Bezichtiging mogelijk. Postcode 1012AB. Neem contact op via WhatsApp.
        Borg: € 1300.
        TXT;

        $data = new ParsedListingInput(null, 650, '06-12345678', $text);
        $out = $service->assess($data);

        $this->assertSame('strong', $out['tier']);
        $this->assertGreaterThanOrEqual(68, $out['score']);
        $this->assertContains('has_price', $out['reason_codes']);
    }

    public function test_random_short_text_scores_weak(): void
    {
        $service = new ListingContentFitService();
        $data = new ParsedListingInput(null, null, null, 'ok');

        $out = $service->assess($data);

        $this->assertSame('weak', $out['tier']);
        $this->assertContains('sparse_text', $out['reason_codes']);
    }

    public function test_known_platform_url_boosts_score(): void
    {
        $service = new ListingContentFitService();
        $text = str_repeat(
            'Informatie over deze woning. Te huur beschikbaar. ',
            8
        );

        $data = new ParsedListingInput('https://www.pararius.nl/huur/amsterdam/appartement/', 950, null, $text);
        $out = $service->assess($data);

        $this->assertContains('known_rental_platform', $out['reason_codes']);
        $this->assertGreaterThanOrEqual(42, $out['score']);
    }
}
