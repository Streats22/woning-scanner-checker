<?php

namespace App\Support;

use App\Services\ListingDwellingClassifier;

/**
 * Vertaalde weergave van {@see ListingDwellingClassifier}-output voor Blade (PDF + web).
 */
final class ListingDwellingPresentation
{
    /**
     * @param  array<string, mixed>  $dwelling
     * @return array{kind_line: string, sector_line: string, signals: ?string}
     */
    public static function lines(array $dwelling): array
    {
        $kindLine = self::joinLabelConfidence(
            self::kindLabel((string) ($dwelling['kind'] ?? 'unknown')),
            self::confidenceLabel((string) ($dwelling['kind_confidence'] ?? 'low')),
        );

        $sectorLine = self::joinLabelConfidence(
            self::sectorLabel((string) ($dwelling['rental_sector'] ?? 'unknown')),
            self::confidenceLabel((string) ($dwelling['sector_confidence'] ?? 'low')),
        );

        $signals = $dwelling['signals'] ?? null;
        $signalsStr = is_array($signals) && $signals !== []
            ? implode(', ', array_map(static fn ($s) => is_string($s) ? $s : '', $signals))
            : null;

        return [
            'kind_line' => $kindLine,
            'sector_line' => $sectorLine,
            'signals' => $signalsStr !== '' ? $signalsStr : null,
        ];
    }

    private static function joinLabelConfidence(string $label, string $confidence): string
    {
        return $label.' · '.$confidence;
    }

    private static function kindLabel(string $kind): string
    {
        return match ($kind) {
            'room' => __('pdf.dwelling_kind_room'),
            'whole' => __('pdf.dwelling_kind_whole'),
            default => __('pdf.dwelling_kind_unknown'),
        };
    }

    private static function sectorLabel(string $sector): string
    {
        return match ($sector) {
            'private' => __('pdf.dwelling_sector_private'),
            'social' => __('pdf.dwelling_sector_social'),
            default => __('pdf.dwelling_sector_unknown'),
        };
    }

    private static function confidenceLabel(string $c): string
    {
        return match ($c) {
            'high' => __('pdf.dwelling_confidence_high'),
            'medium' => __('pdf.dwelling_confidence_medium'),
            default => __('pdf.dwelling_confidence_low'),
        };
    }
}
