<?php

namespace App\Support;

/**
 * Model-maandhuur (€) voor vergelijking in de regel-engine — orde-grootte vrije sector,
 * geen taxatie. Waarden zijn afgeronde indicaties (o.a. Pararius/CBS-achtige orde 2024–2026).
 *
 * Dekking: alle Nederlandse gemeenten uit de Wikipedia-lijst (zie data/nl_rent_benchmarks.php),
 * met provincie-defaults en handmatige scherpstellingen via scripts/generate_nl_rent_benchmarks.py.
 * Onderhoud: generator draaien na gemeentelijke herindeling; zo nodig MANUAL_OVERRIDES in het script bijwerken.
 */
final class RentBenchmarkMap
{
    /** Landelijk gemiddelde / fallback als plaats niet herkend wordt. */
    public const DEFAULT_AVERAGE = 1250;

    /**
     * @return array<string, int> canonieke plaatsnaam => €/maand model
     */
    public static function benchmarks(): array
    {
        return self::loaded()['benchmarks'];
    }

    /**
     * Aliassen → canonieke sleutel in benchmarks() (zelfde spelling als key).
     *
     * @return array<string, string>
     */
    public static function aliases(): array
    {
        return self::loaded()['aliases'];
    }

    public static function averageFor(?string $canonicalCity): int
    {
        if ($canonicalCity === null || $canonicalCity === '') {
            return self::DEFAULT_AVERAGE;
        }

        $map = self::benchmarks();

        return $map[$canonicalCity] ?? self::DEFAULT_AVERAGE;
    }

    /**
     * @return list<string> alle te zoeken plaatsnamen (canonical + aliassen), langste eerst
     */
    public static function searchNeedlesSorted(): array
    {
        $needles = array_keys(self::benchmarks());
        foreach (self::aliases() as $alias => $_canonical) {
            $needles[] = $alias;
        }

        $needles = array_values(array_unique($needles));
        usort($needles, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        return $needles;
    }

    /**
     * @return array<string, string> needle (exact zoals in tekst) => canonieke stad voor benchmark
     */
    public static function needleToCanonical(): array
    {
        $map = [];
        foreach (array_keys(self::benchmarks()) as $city) {
            $map[$city] = $city;
        }
        foreach (self::aliases() as $alias => $canonical) {
            $map[$alias] = $canonical;
        }

        return $map;
    }

    /**
     * @return array{benchmarks: array<string, int>, aliases: array<string, string>}
     */
    private static function loaded(): array
    {
        static $data;

        return $data ??= require dirname(__DIR__, 2).'/data/nl_rent_benchmarks.php';
    }
}
