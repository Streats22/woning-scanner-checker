<?php

namespace Tests\Unit;

use App\Data\ParsedListingInput;
use App\Services\ListingDwellingClassifier;
use App\Services\ReportEnrichmentService;
use Tests\TestCase;

class ReportEnrichmentServiceTest extends TestCase
{
    private function extras(string $text, ?int $price = null, ?float $surface = null): array
    {
        $service = new ReportEnrichmentService(new ListingDwellingClassifier());
        $market = ['average' => 1490, 'difference_percent' => null, 'room_average' => 710];
        $rules = ['score' => 0, 'flags' => [], 'breakdown' => []];

        return $service->buildRuleBasedExtras(new ParsedListingInput(null, $price, null, $text, $surface), $market, $rules);
    }

    private function verifyText(array $extras): string
    {
        return implode("\n", $extras['what_to_verify']);
    }

    public function test_missing_energy_label_is_pointed_out(): void
    {
        $extras = $this->extras('Appartement te huur in Rotterdam, 70 m², € 1.600 per maand.', 1600, 70.0);

        $this->assertStringContainsString('geen energielabel', $this->verifyText($extras));
    }

    public function test_poor_energy_label_mentions_points_and_rent_check(): void
    {
        $extras = $this->extras('Appartement Rotterdam, 60 m², energielabel F, € 1.350 per maand.', 1350, 60.0);
        $verify = $this->verifyText($extras);

        $this->assertStringContainsString('Energielabel F', $verify);
        $this->assertStringContainsString('-9 punten', $verify);
        $this->assertStringContainsString('huurprijscheck', $verify);
        $this->assertStringContainsString('Energielabel F', $extras['market_context']);
    }

    public function test_good_energy_label_adds_market_note_without_warning(): void
    {
        $extras = $this->extras('Appartement Rotterdam, 80 m², energielabel A, € 1.900 per maand.', 1900, 80.0);

        $this->assertStringNotContainsString('energielabel', mb_strtolower($this->verifyText($extras)));
        $this->assertStringContainsString('Energielabel A', $extras['market_context']);
    }

    public function test_temporary_contract_is_pointed_out(): void
    {
        $extras = $this->extras('Appartement Rotterdam, energielabel B, tijdelijk huurcontract voor 1 jaar, € 1.700.', 1700);

        $this->assertStringContainsString('tijdelijk contract', $this->verifyText($extras));
    }

    public function test_room_market_context_mentions_median_room_rent(): void
    {
        $extras = $this->extras('Studentenkamer te huur in Rotterdam, huisgenoten, € 600 p.m.', 600);

        $this->assertStringContainsString('€710', $extras['market_context']);
    }
}
