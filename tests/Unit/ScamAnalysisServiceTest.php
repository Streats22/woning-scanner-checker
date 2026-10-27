<?php

namespace Tests\Unit;

use App\Data\ParsedListingInput;
use App\Services\ListingDwellingClassifier;
use App\Services\ScamAnalysisService;
use Tests\TestCase;

class ScamAnalysisServiceTest extends TestCase
{
    private function market(int $avg = 1500, ?int $roomAverage = null): array
    {
        $market = ['average' => $avg, 'difference_percent' => null];
        if ($roomAverage !== null) {
            $market['room_average'] = $roomAverage;
        }

        return $market;
    }

    private function analyze(string $text, ?int $price = null, ?float $surface = null, ?array $market = null, ?string $url = null): array
    {
        $s = new ScamAnalysisService(new ListingDwellingClassifier());

        return $s->analyze(new ParsedListingInput($url, $price, null, $text, $surface), $market ?? $this->market());
    }

    public function test_price_under_benchmark_adds_thirty_points(): void
    {
        $s = new ScamAnalysisService(new ListingDwellingClassifier());
        $data = new ParsedListingInput(null, 500, null, 'Appartement Amsterdam');
        $out = $s->analyze($data, $this->market(1850));

        $this->assertGreaterThanOrEqual(30, $out['score']);
        $this->assertContains('Prijs significant onder marktwaarde', $out['flags']);
    }

    public function test_typical_room_price_does_not_trigger_under_benchmark_points(): void
    {
        $s = new ScamAnalysisService(new ListingDwellingClassifier());
        $data = new ParsedListingInput(
            'https://kamernet.nl/huren/kamer-amsterdam/x',
            550,
            null,
            'Lichte studentenkamer te huur in Amsterdam, €550 p.m. Huisgenoten.'
        );
        $out = $s->analyze($data, $this->market(1850));

        $this->assertLessThan(30, $out['score']);
        $this->assertNotContains('Prijs significant onder marktwaarde', $out['flags']);
    }

    public function test_extreme_low_room_price_still_triggers_under_benchmark_points(): void
    {
        $s = new ScamAnalysisService(new ListingDwellingClassifier());
        $data = new ParsedListingInput(null, 150, null, 'Studentenkamer te huur, €150 p.m. Amsterdam');
        $out = $s->analyze($data, $this->market(1850));

        $this->assertGreaterThanOrEqual(30, $out['score']);
        $this->assertContains('Prijs significant onder marktwaarde', $out['flags']);
    }

    public function test_telegram_adds_messaging_points(): void
    {
        $s = new ScamAnalysisService(new ListingDwellingClassifier());
        $data = new ParsedListingInput(null, null, null, 'Reageer via Telegram voor snelle reactie.');
        $out = $s->analyze($data, $this->market());

        $this->assertContains('Alternatieve chat-app (Telegram/Signal/WeChat/Skype)', $out['flags']);
        $this->assertGreaterThanOrEqual(8, $out['score']);
    }

    public function test_crypto_triggers_high_risk_payment(): void
    {
        $s = new ScamAnalysisService(new ListingDwellingClassifier());
        $data = new ParsedListingInput(null, null, null, 'Betaal de borg in USDT naar ons walletadres.');
        $out = $s->analyze($data, $this->market());

        $this->assertContains('Hoog-risico betaalpatroon', $out['flags']);
        $this->assertGreaterThanOrEqual(40, $out['score']);
    }

    public function test_no_viewing_and_abroad_narrative(): void
    {
        $s = new ScamAnalysisService(new ListingDwellingClassifier());
        $data = new ParsedListingInput(null, null, null, 'Geen bezichtiging mogelijk, ik woon in het buitenland voor mijn werk.');
        $out = $s->analyze($data, $this->market());

        $this->assertContains('Geen echte bezichtiging of afstands-verhaal', $out['flags']);
    }

    public function test_google_forms_flag(): void
    {
        $s = new ScamAnalysisService(new ListingDwellingClassifier());
        $data = new ParsedListingInput(null, null, null, 'Meld je aan via https://forms.gle/abc123');
        $out = $s->analyze($data, $this->market());

        $this->assertContains('Aanmelding via extern webformulier', $out['flags']);
    }

    public function test_bemiddelingskosten_studentenkamer_triggers_upfront_fee(): void
    {
        $s = new ScamAnalysisService(new ListingDwellingClassifier());
        $data = new ParsedListingInput(null, null, null, 'Eenmalige bemiddelingskosten voor deze studentenkamer graag vooraf overmaken.');
        $out = $s->analyze($data, $this->market());

        $this->assertContains('Geld vragen voor bezichtiging of reservering', $out['flags']);
    }

    public function test_id_ruil_student_scam_flag(): void
    {
        $s = new ScamAnalysisService(new ListingDwellingClassifier());
        $data = new ParsedListingInput(null, null, null, 'Stuur een kopie paspoort, we wisselen ID uit zodat je mij kunt vertrouwen als bewijs.');
        $out = $s->analyze($data, $this->market());

        $this->assertContains('ID uitwisselen met onbekende verhuurder (studenten-/kamer-scam)', $out['flags']);
    }

    public function test_score_caps_at_one_hundred(): void
    {
        $s = new ScamAnalysisService(new ListingDwellingClassifier());
        $text = implode(' ', [
            'Amsterdam',
            '€ 400 per maand',
            'WhatsApp',
            'Telegram',
            'Western Union',
            'alleen vandaag',
            'geen bezichtiging',
            'forms.gle/x',
            'wegens verhuizing naar het buitenland',
            'Kind regards',
            'bezichtigingskosten vooraf',
        ]);
        $data = new ParsedListingInput(null, 400, null, $text);
        $out = $s->analyze($data, $this->market(1850));

        $this->assertSame(100, $out['score']);
    }

    public function test_room_price_uses_median_room_rent_when_available(): void
    {
        $out = $this->analyze('Studentenkamer te huur in Amsterdam, €450 p.m.', 450, null, $this->market(1860, 950));
        $this->assertContains('Prijs significant onder marktwaarde', $out['flags']);

        $out = $this->analyze('Studentenkamer te huur in Amsterdam, €600 p.m.', 600, null, $this->market(1860, 950));
        $this->assertNotContains('Prijs significant onder marktwaarde', $out['flags']);
    }

    public function test_regulated_mid_rent_without_surface_is_not_flagged_as_too_cheap(): void
    {
        $out = $this->analyze('Appartement te huur in Amsterdam, € 1.150 per maand', 1150, null, $this->market(1860));

        $this->assertNotContains('Prijs significant onder marktwaarde', $out['flags']);
    }

    public function test_per_m2_comparison_used_when_surface_known(): void
    {
        /* Studio van 30 m² voor € 1.000: per maand ver onder het model, per m² marktconform. */
        $out = $this->analyze('Studio Amsterdam, woonoppervlakte 30 m², € 1.000', 1000, 30.0, $this->market(1860));
        $this->assertNotContains('Prijs significant onder marktwaarde', $out['flags']);

        /* 90 m² voor € 800 ligt ruim 45% onder de €/m²-benchmark. */
        $out = $this->analyze('Appartement Amsterdam, 90 m², € 800', 800, 90.0, $this->market(1860));
        $this->assertContains('Prijs significant onder marktwaarde', $out['flags']);
    }

    public function test_social_housing_is_not_flagged_as_too_cheap(): void
    {
        $out = $this->analyze('Sociale huurwoning van woningcorporatie via WoningNet, inschrijfduur vereist, € 650 kale huur, huurtoeslag mogelijk.', 650, null, $this->market(1860));

        $this->assertNotContains('Prijs significant onder marktwaarde', $out['flags']);
    }

    public function test_airbnb_payment_proxy_flag(): void
    {
        $out = $this->analyze('Na akkoord betaal je de eerste maand veilig via Airbnb, ik stuur je de link.');

        $this->assertContains('Betalen “via Airbnb/Booking” of een platform-betaallink', $out['flags']);
        $this->assertGreaterThanOrEqual(35, $out['score']);
    }

    public function test_foreign_iban_flag(): void
    {
        $out = $this->analyze('Maak de borg over naar DE89 3704 0044 0532 0130 00 t.n.v. J. Smit.');
        $this->assertContains('Betaling naar een buitenlandse rekening', $out['flags']);

        $out = $this->analyze('Maak de borg over naar NL91 ABNA 0417 1643 00 t.n.v. J. Smit.');
        $this->assertNotContains('Betaling naar een buitenlandse rekening', $out['flags']);
    }

    public function test_deposit_above_two_months_flag(): void
    {
        $out = $this->analyze('Huur € 1.500 per maand. Borg: 3 maanden huur.', 1500);
        $this->assertContains('Borg hoger dan wettelijk toegestaan (max. 2 maanden)', $out['flags']);

        $out = $this->analyze('Huur € 1.500 per maand, waarborgsom € 4.500.', 1500);
        $this->assertContains('Borg hoger dan wettelijk toegestaan (max. 2 maanden)', $out['flags']);

        $out = $this->analyze('Huur € 1.500 per maand, borg 2 maanden huur.', 1500);
        $this->assertNotContains('Borg hoger dan wettelijk toegestaan (max. 2 maanden)', $out['flags']);
    }

    public function test_tenant_fees_flag_ignores_negations(): void
    {
        $out = $this->analyze('Bij ondertekening betaal je € 350 contractkosten.');
        $this->assertContains('Bemiddelings- of contractkosten voor de huurder (verboden)', $out['flags']);

        $out = $this->analyze('Geen bemiddelingskosten, direct van de eigenaar.');
        $this->assertNotContains('Bemiddelings- of contractkosten voor de huurder (verboden)', $out['flags']);
    }

    public function test_pay_first_counts_as_money_before_viewing(): void
    {
        $out = $this->analyze('Eerst betalen en dan krijg je de info over de bezichtiging.');

        $this->assertContains('Geld vragen voor bezichtiging of reservering', $out['flags']);
    }

    public function test_verification_phishing_flag(): void
    {
        $out = $this->analyze('Verifieer binnen 24 uur je betaalgegevens via deze link, anders vervalt je reservering.');

        $this->assertContains('Verzoek om gegevens te “verifiëren” (phishing-patroon)', $out['flags']);
    }

    public function test_keys_sent_by_post_counts_as_no_viewing(): void
    {
        $out = $this->analyze('Na betaling worden de sleutels opgestuurd.');

        $this->assertContains('Geen echte bezichtiging of afstands-verhaal', $out['flags']);
    }
}
