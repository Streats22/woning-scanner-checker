<?php

namespace App\Support;

/**
 * Vergelijkt maandhuur per m² met een grove afgeleide van de model-maandhuur.
 *
 * De model-maandhuur (€/maand) wordt gedeeld door een vaste referentie-oppervlakte (m²)
 * om een orde-grootte €/m²/maand te krijgen — geen taxatie.
 *
 * Voor kleine oppervlaktes (kamers/studio's) wordt de referentie-€/m² opgeschaald met √(ref/S),
 * omdat kleine eenheden in de praktijk vaak een hogere €/m² hebben.
 */
final class RentPerSquareMeterModel
{
    /** m² — zelfde orde als een gemiddeld appartement; alleen voor €/m²-schatting. */
    public const REFERENCE_SURFACE_M2 = 55.0;

    /** Onder deze m² tonen we een extra contexttekst (kamers / kleine units). */
    public const SMALL_SURFACE_THRESHOLD_M2 = 28.0;

    /**
     * @return array{
     *     surface_m2: float|null,
     *     price_per_m2_month_eur: int|null,
     *     benchmark_per_m2_month_eur: int,
     *     adjusted_benchmark_per_m2_month_eur: float|null,
     *     per_m2_vs_adjusted_percent: int|null,
     *     small_surface: bool
     * }
     */
    public static function build(?int $priceMonthly, ?float $surfaceM2, int $benchmarkMonthly): array
    {
        $benchmarkPerM2 = $benchmarkMonthly / self::REFERENCE_SURFACE_M2;

        $pricePerM2 = null;
        $adjustedBenchmarkPerM2 = null;
        $vsPercent = null;
        $small = $surfaceM2 !== null && $surfaceM2 < self::SMALL_SURFACE_THRESHOLD_M2;

        if ($priceMonthly !== null && $priceMonthly > 0 && $surfaceM2 !== null && $surfaceM2 > 0) {
            $pricePerM2 = (int) round($priceMonthly / $surfaceM2);
            $s = max($surfaceM2, 8.0);
            $adjustedBenchmarkPerM2 = $benchmarkPerM2 * sqrt(self::REFERENCE_SURFACE_M2 / $s);
            if ($adjustedBenchmarkPerM2 > 0) {
                $vsPercent = (int) round((($priceMonthly / $surfaceM2) - $adjustedBenchmarkPerM2) / $adjustedBenchmarkPerM2 * 100);
            }
        }

        return [
            'surface_m2' => $surfaceM2,
            'price_per_m2_month_eur' => $pricePerM2,
            'benchmark_per_m2_month_eur' => (int) round($benchmarkPerM2),
            'adjusted_benchmark_per_m2_month_eur' => $adjustedBenchmarkPerM2 !== null ? round($adjustedBenchmarkPerM2, 2) : null,
            'per_m2_vs_adjusted_percent' => $vsPercent,
            'small_surface' => $small,
        ];
    }
}
