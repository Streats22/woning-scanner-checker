<?php

namespace App\Support;

/**
 * Energielabel uit advertentietekst + effect op de WWS-puntentelling (stand 2026, labels vanaf 2021 / NTA 8800).
 *
 * In de vrije sector heeft het label geen aantoonbaar structureel effect op de vraaghuur (Colliers 2023,
 * ±75.000 NVM-transacties); het telt wél direct mee in de puntentelling (Wet betaalbare huur). Daarom gebruiken
 * we het als regelgevings-signaal, niet om de marktbenchmark bij te stellen.
 *
 * Bron punten: Rijksoverheid, "Welke invloed heeft het energielabel op de huurpunten van mijn woning?" (2026).
 */
final class EnergyLabel
{
    /** Labels van best naar slechtst. */
    public const ORDER = ['A++++', 'A+++', 'A++', 'A+', 'A', 'B', 'C', 'D', 'E', 'F', 'G'];

    /**
     * Zelfstandige woning ≥ 25 m²: [eengezins, meergezins]. 25–40 m² wijkt alleen af bij A++++ meergezins (62).
     *
     * @var array<string, array{0: int, 1: int}>
     */
    private const POINTS_FROM_25_M2 = [
        'A++++' => [62, 58], 'A+++' => [57, 53], 'A++' => [52, 48], 'A+' => [47, 43], 'A' => [41, 37],
        'B' => [34, 30], 'C' => [22, 15], 'D' => [14, 11], 'E' => [-4, -4], 'F' => [-9, -9], 'G' => [-15, -15],
    ];

    /** @var array<string, array{0: int, 1: int}> */
    private const POINTS_UNDER_25_M2 = [
        'A++++' => [62, 62], 'A+++' => [62, 62], 'A++' => [60, 56], 'A+' => [55, 51], 'A' => [49, 45],
        'B' => [42, 38], 'C' => [36, 32], 'D' => [32, 28], 'E' => [-4, -4], 'F' => [-9, -9], 'G' => [-15, -15],
    ];

    /** Onzelfstandige woonruimte (kamer): punten per m². */
    private const POINTS_PER_M2_ROOM = [
        'A++++' => 1.0, 'A+++' => 0.95, 'A++' => 0.85, 'A+' => 0.75, 'A' => 0.65,
        'B' => 0.5, 'C' => 0.35, 'D' => 0.2, 'E' => -0.05, 'F' => -0.1, 'G' => -0.15,
    ];

    /**
     * Zoekt een expliciet genoemd energielabel ("Energielabel: B", "energy label A++", "energieklasse C").
     * Een losse letter zonder label-woord telt niet mee.
     */
    public static function parse(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return null;
        }

        $re = '/(?:energie[\s-]?label|energy[\s-]?(?:label|rating|class)|energieklasse|epc[\s-]?label)'
            .'\s*(?:is|van|klasse|class)?\s*[:=\-–]?\s*["“(]?\s*([A-G])\s*(\+{1,4})?(?![\p{L}\p{N}])/iu';

        if (! preg_match($re, $text, $m)) {
            return null;
        }

        $label = mb_strtoupper($m[1]).($m[2] ?? '');

        return in_array($label, self::ORDER, true) ? $label : null;
    }

    /** E, F en G leveren aftrekpunten op en vallen onder de geplande minimumnorm (label D vanaf 2029). */
    public static function isPoor(string $label): bool
    {
        return in_array($label, ['E', 'F', 'G'], true);
    }

    /**
     * WWS-punten voor het label. Kamers: punten per m² × oppervlakte (null zonder oppervlakte).
     * Zelfstandig: meergezins (appartement) tenzij $singleFamily.
     */
    public static function wwsPoints(string $label, ?float $surfaceM2, bool $room = false, bool $singleFamily = false): ?float
    {
        if (! in_array($label, self::ORDER, true)) {
            return null;
        }

        if ($room) {
            return $surfaceM2 !== null && $surfaceM2 > 0
                ? round(self::POINTS_PER_M2_ROOM[$label] * $surfaceM2, 2)
                : null;
        }

        $table = $surfaceM2 !== null && $surfaceM2 < 25 ? self::POINTS_UNDER_25_M2 : self::POINTS_FROM_25_M2;
        $points = $table[$label][$singleFamily ? 0 : 1];

        if ($label === 'A++++' && ! $singleFamily && $surfaceM2 !== null && $surfaceM2 < 40) {
            $points = 62;
        }

        return (float) $points;
    }
}
