<?php

namespace App\Services\Report;

use App\Data\ParsedListingInput;
use App\Models\Listing;

/**
 * Bouwt report_snapshot en de JSON-payload voor POST /analyze (één plek, geen dubbele veldnamen verspreid).
 */
final class ListingAnalyzeResultAssembler
{
    public function __construct(
        private ListingFactsAssembler $listingFacts,
    ) {}

    /**
     * @param  array<string, mixed>  $analysis
     * @param  array{average: int, difference_percent: ?int}  $priceData
     * @param  array{tier: string, score: int, reason_codes: array<int, string>}  $listingFit
     * @return array<string, mixed>
     */
    public function buildReportSnapshot(array $analysis, array $priceData, array $listingFit, ParsedListingInput $input, ?string $city): array
    {
        return [
            'version' => 2,
            'generated_at' => now()->toIso8601String(),
            'rule_score' => $analysis['rule_score'],
            'llm_used' => $analysis['llm_used'],
            'link_assessment' => $analysis['link_assessment'],
            'recommendations' => $analysis['recommendations'],
            'what_to_verify' => $analysis['what_to_verify'],
            'risk_breakdown' => $analysis['risk_breakdown'],
            'methodology' => $analysis['methodology'],
            'market_context' => $analysis['market_context'],
            'summary_short' => $analysis['summary_short'] ?? null,
            'narrative' => $analysis['narrative'] ?? null,
            'observations' => $analysis['observations'] ?? [],
            'listing_fit' => $listingFit,
            'market' => $priceData,
            'listing_facts' => $this->listingFacts->build($input, $city, $priceData),
        ];
    }

    /**
     * @param  array<string, mixed>  $analysis
     * @param  array{average: int, difference_percent: ?int}  $priceData
     * @param  array{report_url: string, report_pdf_url: string}  $urls
     * @param  array{tier: string, score: int, reason_codes: array<int, string>}  $listingFit
     * @return array<string, mixed>
     */
    public function buildApiPayload(Listing $listing, array $analysis, array $priceData, array $urls, array $listingFit): array
    {
        $facts = is_array($listing->report_snapshot['listing_facts'] ?? null)
            ? $listing->report_snapshot['listing_facts']
            : $this->listingFacts->build(
                new ParsedListingInput(
                    $listing->source_url,
                    $listing->price,
                    $listing->contact,
                    (string) $listing->description,
                ),
                $listing->city,
                [
                    'average' => (int) $listing->market_average,
                    'difference_percent' => $listing->market_difference_percent,
                ],
            );

        return [
            'score' => $analysis['score'],
            'flags' => $analysis['flags'],
            'market' => $priceData,
            'summary' => $analysis['summary'],
            'summary_short' => $analysis['summary_short'] ?? $analysis['summary'],
            'narrative' => $analysis['narrative'] ?? null,
            'observations' => $analysis['observations'] ?? [],
            'llm_used' => $analysis['llm_used'],
            'link_assessment' => $analysis['link_assessment'],
            'recommendations' => $analysis['recommendations'],
            'what_to_verify' => $analysis['what_to_verify'],
            'risk_breakdown' => $analysis['risk_breakdown'],
            'rule_score' => $analysis['rule_score'],
            'methodology' => $analysis['methodology'],
            'market_context' => $analysis['market_context'],
            'listing_fit' => $listingFit,
            'listing_facts' => $facts,
            'id' => $listing->id,
            'report_url' => $urls['report_url'],
            'report_pdf_url' => $urls['report_pdf_url'],
            'report_slug' => $listing->report_slug,
        ];
    }
}
