<?php

namespace Tests\Unit;

use App\Data\ParsedListingInput;
use App\Services\ListingDwellingClassifier;
use App\Services\ScamAnalysisService;
use PHPUnit\Framework\TestCase;

class ScamAnalysisServiceTest extends TestCase
{
    private function market(int $avg = 1500): array
    {
        return ['average' => $avg, 'difference_percent' => null];
    }

    public function test_price_under_benchmark_adds_thirty_points(): void
    {
        $s = new ScamAnalysisService(new ListingDwellingClassifier);
        $data = new ParsedListingInput(null, 500, null, 'Appartement Amsterdam');
        $out = $s->analyze($data, $this->market(1850));

        $this->assertGreaterThanOrEqual(30, $out['score']);
        $this->assertContains('Prijs significant onder marktwaarde', $out['flags']);
    }

    public function test_typical_room_price_does_not_trigger_under_benchmark_points(): void
    {
        $s = new ScamAnalysisService(new ListingDwellingClassifier);
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
        $s = new ScamAnalysisService(new ListingDwellingClassifier);
        $data = new ParsedListingInput(null, 150, null, 'Studentenkamer te huur, €150 p.m. Amsterdam');
        $out = $s->analyze($data, $this->market(1850));

        $this->assertGreaterThanOrEqual(30, $out['score']);
        $this->assertContains('Prijs significant onder marktwaarde', $out['flags']);
    }

    public function test_telegram_adds_messaging_points(): void
    {
        $s = new ScamAnalysisService(new ListingDwellingClassifier);
        $data = new ParsedListingInput(null, null, null, 'Reageer via Telegram voor snelle reactie.');
        $out = $s->analyze($data, $this->market());

        $this->assertContains('Alternatieve chat-app (Telegram/Signal/WeChat/Skype)', $out['flags']);
        $this->assertGreaterThanOrEqual(8, $out['score']);
    }

    public function test_crypto_triggers_high_risk_payment(): void
    {
        $s = new ScamAnalysisService(new ListingDwellingClassifier);
        $data = new ParsedListingInput(null, null, null, 'Betaal de borg in USDT naar ons walletadres.');
        $out = $s->analyze($data, $this->market());

        $this->assertContains('Hoog-risico betaalpatroon', $out['flags']);
        $this->assertGreaterThanOrEqual(40, $out['score']);
    }

    public function test_no_viewing_and_abroad_narrative(): void
    {
        $s = new ScamAnalysisService(new ListingDwellingClassifier);
        $data = new ParsedListingInput(null, null, null, 'Geen bezichtiging mogelijk, ik woon in het buitenland voor mijn werk.');
        $out = $s->analyze($data, $this->market());

        $this->assertContains('Geen echte bezichtiging of afstands-verhaal', $out['flags']);
    }

    public function test_google_forms_flag(): void
    {
        $s = new ScamAnalysisService(new ListingDwellingClassifier);
        $data = new ParsedListingInput(null, null, null, 'Meld je aan via https://forms.gle/abc123');
        $out = $s->analyze($data, $this->market());

        $this->assertContains('Aanmelding via extern webformulier', $out['flags']);
    }

    public function test_bemiddelingskosten_studentenkamer_triggers_upfront_fee(): void
    {
        $s = new ScamAnalysisService(new ListingDwellingClassifier);
        $data = new ParsedListingInput(null, null, null, 'Eenmalige bemiddelingskosten voor deze studentenkamer graag vooraf overmaken.');
        $out = $s->analyze($data, $this->market());

        $this->assertContains('Geld vragen vóór bezichtiging of reservering', $out['flags']);
    }

    public function test_id_ruil_student_scam_flag(): void
    {
        $s = new ScamAnalysisService(new ListingDwellingClassifier);
        $data = new ParsedListingInput(null, null, null, 'Stuur een kopie paspoort, we wisselen ID uit zodat je mij kunt vertrouwen als bewijs.');
        $out = $s->analyze($data, $this->market());

        $this->assertContains('ID uitwisselen met onbekende verhuurder (studenten-/kamer-scam)', $out['flags']);
    }

    public function test_score_caps_at_one_hundred(): void
    {
        $s = new ScamAnalysisService(new ListingDwellingClassifier);
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
}
