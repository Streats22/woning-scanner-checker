<?php

namespace App\Services;

use App\Data\ParsedListingInput;
use App\Support\EnergyLabel;
use App\Support\ListingDwellingRecommendationHints;
use App\Support\RentRegulation;

class ReportEnrichmentService
{
    public function __construct(
        private ListingDwellingClassifier $dwellingClassifier,
    )
    {
    }

    /**
     * Vult aanbevelingen en controles op basis van regels + score (zonder LLM-extras).
     *
     * @param  array{average: int, difference_percent: ?int, room_average?: int}  $market
     * @param  array{score: int, flags: array<int, string>, breakdown: array<int, array{category: string, points: int, detail: string}>}  $ruleScam
     * @return array{recommendations: array<int, string>, what_to_verify: array<int, string>, market_context: string, methodology: string}
     */
    public function buildRuleBasedExtras(ParsedListingInput $data, array $market, array $ruleScam): array
    {
        $recommendations = [];
        $checks = [];
        $dwelling = $this->dwellingClassifier->classify($data);

        if ($ruleScam['score'] >= 61) {
            $recommendations[] = __('report_enrichment.rec_high_1');
            $recommendations[] = __('report_enrichment.rec_high_2');
        } elseif ($ruleScam['score'] >= 31) {
            $recommendations[] = __('report_enrichment.rec_mid_1');
        } else {
            $recommendations[] = __('report_enrichment.rec_low_1');
        }

        if ($data->sourceUrl) {
            $checks[] = __('report_enrichment.check_url_1');
            $checks[] = __('report_enrichment.check_url_2');
        } else {
            $checks[] = __('report_enrichment.check_no_url_1');
        }

        $flagLine = implode(' ', $ruleScam['flags']);
        if (preg_match('/WhatsApp|Telegram|Signal|WeChat|Skype|chat-app/i', $flagLine)) {
            $checks[] = __('report_enrichment.check_chat_apps');
        }

        $cheapVsBenchmark = $data->price && $market['difference_percent'] !== null && $market['difference_percent'] < -25;
        if ($cheapVsBenchmark && $dwelling['kind'] !== 'room') {
            $checks[] = __('report_enrichment.check_price_compare');
        }
        if ($cheapVsBenchmark && $dwelling['kind'] === 'room' && $market['difference_percent'] < -65) {
            $checks[] = __('report_enrichment.check_room_price_extreme');
        }

        $checks[] = __('report_enrichment.check_no_wu');
        $checks[] = __('report_enrichment.check_id_landlord');
        $checks[] = __('report_enrichment.check_legal_costs', ['max' => RentRegulation::MAX_DEPOSIT_MONTHS]);

        $energyLabel = EnergyLabel::parse($data->description);
        foreach ($this->regulationChecks($data, $dwelling, $energyLabel) as $check) {
            $checks[] = $check;
        }

        foreach (ListingDwellingRecommendationHints::contextualVerifyChecks($dwelling) as $hint) {
            $checks[] = $hint;
        }

        $suffix = $market['difference_percent'] !== null
            ? __('report_enrichment.market_diff_suffix', ['pct' => $market['difference_percent']])
            : '.';

        $cityLine = $market['average']
            ? __('report_enrichment.market_city_line', [
                'avg' => number_format($market['average'], 0, ',', '.'),
                'suffix' => $suffix,
            ])
            : '';

        $roomBenchNote = '';
        if ($dwelling['kind'] === 'room') {
            $roomBenchNote = __('report_enrichment.room_benchmark_note');
            if (! empty($market['room_average'])) {
                $roomBenchNote .= __('report_enrichment.market_room_line', [
                    'avg' => number_format($market['room_average'], 0, ',', '.'),
                ]);
            }
        }

        $labelNote = $energyLabel !== null
            ? __(EnergyLabel::isPoor($energyLabel) ? 'report_enrichment.energy_label_note_poor' : 'report_enrichment.energy_label_note', ['label' => $energyLabel])
            : '';

        $marketContext = $cityLine.$roomBenchNote.$labelNote.__('report_enrichment.market_footer');

        return [
            'recommendations' => array_values(array_unique($recommendations)),
            'what_to_verify' => array_values(array_unique($checks)),
            'market_context' => $marketContext,
            'methodology' => __('report_enrichment.methodology'),
        ];
    }

    /**
     * Controles op basis van huurregels 2026 (energielabel, Wet betaalbare huur, Wet vaste huurcontracten).
     * Deze tellen niet mee in de risicoscore: het zijn rechten van de huurder, geen fraudesignalen.
     *
     * @param  array{kind: string, rental_sector: string}  $dwelling
     * @return list<string>
     */
    private function regulationChecks(ParsedListingInput $data, array $dwelling, ?string $energyLabel): array
    {
        $checks = [];
        $isRoom = $dwelling['kind'] === 'room';

        if ($energyLabel === null) {
            if (! $isRoom) {
                $checks[] = __('report_enrichment.check_energy_label_missing');
            }
        } elseif (EnergyLabel::isPoor($energyLabel)) {
            $points = EnergyLabel::wwsPoints($energyLabel, $data->surfaceM2, $isRoom);
            $checks[] = __('report_enrichment.check_energy_label_poor', [
                'label' => $energyLabel,
                'points' => $points !== null ? number_format($points, $isRoom ? 1 : 0, ',', '.') : '-',
                'year' => RentRegulation::MINIMUM_LABEL_D_FROM_YEAR,
            ]);
        }

        /*
         * Huur tot (net boven) de middenhuurgrens: de puntentelling bepaalt de maximale huur. Bij een slecht label
         * kan een woning met een hogere huur ook onder de middenhuur vallen, daarom een ruimere marge.
         */
        $regulatedCeiling = RentRegulation::MID_RENT_MAX_EUR * ($energyLabel !== null && EnergyLabel::isPoor($energyLabel) ? 1.25 : 1.0);
        if (! $isRoom && $data->price !== null && $data->price > 0 && $data->price <= $regulatedCeiling) {
            $checks[] = __('report_enrichment.check_regulated_rent', [
                'social' => RentRegulation::formatEur(RentRegulation::SOCIAL_RENT_MAX_EUR),
                'mid' => RentRegulation::formatEur(RentRegulation::MID_RENT_MAX_EUR),
            ]);
        }

        if (preg_match('/tijdelijke?\s+(huur)?(contract|overeenkomst|verhuur)|tijdelijk\s+te\s+huur|\bmax(imaal|\.)?\s+\d{1,2}\s+maanden\s+(te\s+)?huren|temporary\s+(rental\s+|lease\s+)?(contract|agreement|lease)/iu', $data->description)) {
            $checks[] = __('report_enrichment.check_temporary_contract');
        }

        return $checks;
    }
}
