<?php

namespace App\Services\Report;

use App\Models\Listing;

/**
 * Bouwt report_snapshot en de JSON-payload voor POST /analyze (één plek, geen dubbele veldnamen verspreid).
 */
final class ListingAnalyzeResultAssembler
{
    /**
     * @param  array<string, mixed>  $analysis
     * @param  array{average: int, difference_percent: ?int}  $priceData
     * @param  array{tier: string, score: int, reason_codes: array<int, string>}  $listingFit
     * @return array<string, mixed>
     */
    public function buildReportSnapshot(array $analysis, array $priceData, array $listingFit): array
    {
        return [
            'version' => 1,
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
            'id' => $listing->id,
            'report_url' => $urls['report_url'],
            'report_pdf_url' => $urls['report_pdf_url'],
            'report_slug' => $listing->report_slug,
        ];
    }
}
