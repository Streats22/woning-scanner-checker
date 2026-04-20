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
            $out[] = 'Bij een kamer: check huisgenoten en huisregels, en vraag schriftelijk wat er in de huur is inbegrepen (servicekosten, internet).';
        }

        if (self::isConfidentSocial($dwelling)) {
            $out[] = 'Bij sociale huur: controleer of de aanbieder echt een woningcorporatie of erkend toewijzingskanaal is; let op phishing met vervalste “Woonnet”-links.';
        }

        if (self::isConfidentPrivate($dwelling)) {
            $out[] = 'Bij particuliere verhuur: controleer eigendom (Kadaster) en identiteit van de verhuurder; wees extra alert op vooruitbetaling zonder bezichtiging.';
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
