<?php

namespace App\Services;

use App\Data\ParsedListingInput;
use App\Support\ListingDwellingRecommendationHints;

class ReportEnrichmentService
{
    public function __construct(
        private ListingDwellingClassifier $dwellingClassifier,
    ) {}

    /**
     * Vult aanbevelingen en controles op basis van regels + score (zonder LLM-extras).
     *
     * @param  array{average: int, difference_percent: ?int}  $market
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

        $roomBenchNote = $dwelling['kind'] === 'room'
            ? __('report_enrichment.room_benchmark_note')
            : '';

        $marketContext = $cityLine.$roomBenchNote.__('report_enrichment.market_footer');

        return [
            'recommendations' => array_values(array_unique($recommendations)),
            'what_to_verify' => array_values(array_unique($checks)),
            'market_context' => $marketContext,
            'methodology' => __('report_enrichment.methodology'),
        ];
    }
}
