<?php

namespace App\Support;

/**
 * Optionele controle-regels op basis van woning-classificatie (geen dubbele logica in ReportEnrichmentService).
 *
 * @phpstan-type DwellingArray array{
 *     kind?: string,
 *     kind_confidence?: string,
 *     rental_sector?: string,
 *     sector_confidence?: string,
 * }
 */
final class ListingDwellingRecommendationHints
{
    /**
     * @param  DwellingArray  $dwelling
     * @return array<int, string>
     */
    public static function contextualVerifyChecks(array $dwelling): array
    {
        $out = [];

        if (self::isConfidentRoom($dwelling)) {
            $out[] = __('report_enrichment.hint_room');
        }

        if (self::isConfidentSocial($dwelling)) {
            $out[] = __('report_enrichment.hint_social');
        }

        if (self::isConfidentPrivate($dwelling)) {
            $out[] = __('report_enrichment.hint_private');
        }

        return $out;
    }

    /**
     * @param  DwellingArray  $dwelling
     */
    private static function isConfidentRoom(array $dwelling): bool
    {
        return ($dwelling['kind'] ?? '') === 'room'
            && ($dwelling['kind_confidence'] ?? '') !== 'low';
    }

    /**
     * @param  DwellingArray  $dwelling
     */
    private static function isConfidentSocial(array $dwelling): bool
    {
        return ($dwelling['rental_sector'] ?? '') === 'social'
            && ($dwelling['sector_confidence'] ?? '') !== 'low';
    }

    /**
     * @param  DwellingArray  $dwelling
     */
    private static function isConfidentPrivate(array $dwelling): bool
    {
        return ($dwelling['rental_sector'] ?? '') === 'private'
            && ($dwelling['sector_confidence'] ?? '') !== 'low';
    }
}
