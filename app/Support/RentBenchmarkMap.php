<?php

namespace App\Support;

/**
 * Model-maandhuur (€) voor vergelijking in de regel-engine — orde-grootte vrije sector,
 * geen taxatie. Waarden zijn afgeronde indicaties (o.a. Pararius/CBS-achtige orde 2024–2026).
 *
 * Dekking: alle Nederlandse gemeenten uit de Wikipedia-lijst (zie data/nl_rent_benchmarks.php),
 * met provincie-defaults en handmatige scherpstellingen via scripts/generate_nl_rent_benchmarks.py.
 * Onderhoud: generator draaien na gemeentelijke herindeling; zo nodig MANUAL_OVERRIDES in het script bijwerken.
 * Controlelijst alle waarden: `php artisan rent:benchmarks:list` (of `--csv`).
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
     * Map een URL-padsegment (bijv. huur/alkmaar/…, kamer-bussum, appartement-rotterdam-centrum)
     * naar de canonieke gemeentenaam.
     *
     * Werkt voor: volledige slug gelijk aan gemeente/alias, én samengestelde slugs waarin een
     * plaatsnaam als los woord voorkomt (veel verhuurplatformen: kamer-{plaats}/…).
     */
    public static function canonicalFromPathSegment(string $segment): ?string
    {
        $segment = trim(rawurldecode($segment));
        if ($segment === '' || mb_strlen($segment) < 3) {
            return null;
        }

        $norm = mb_strtolower(str_replace(['-', '_'], ' ', $segment), 'UTF-8');
        $norm = preg_replace('/\s+/u', ' ', $norm) ?? $norm;

        foreach (array_keys(self::benchmarks()) as $city) {
            if (mb_strtolower($city, 'UTF-8') === $norm) {
                return $city;
            }
        }

        foreach (self::aliases() as $alias => $canonical) {
            if (mb_strtolower($alias, 'UTF-8') === $norm) {
                return $canonical;
            }
        }

        /* Alleen cijfers (bijv. kamer-2371566) — geen gemeente */
        if (preg_match('/^[\d\s]+$/u', $norm)) {
            return null;
        }

        /*
         * Samengevoegde pad-segmenten: elke bekende plaatsnaam (langste eerst) als heel woord.
         * Voorkomt dat platforms zoals Kamernet (/huren/kamer-bussum/…) op de HTML (nav met o.a. Utrecht)
         * terugvallen i.p.v. de echte plaats in de URL.
         */
        $needleToCanonical = self::needleToCanonical();
        foreach (self::searchNeedlesSorted() as $needle) {
            $n = mb_strtolower($needle, 'UTF-8');
            if (mb_strlen($n) < 3) {
                continue;
            }
            $pattern = '/(?<![\p{L}\p{N}])'.preg_quote($n, '/').'(?![\p{L}\p{N}])/u';
            if (preg_match($pattern, $norm)) {
                return $needleToCanonical[$needle];
            }
        }

        return null;
    }

    /**
     * Zet een opgeslagen of gedetecteerde waarde om naar de canonieke benchmarksleutel
     * (zelfde als in benchmarks() of via alias).
     */
    public static function resolveCanonicalCity(?string $city): ?string
    {
        if ($city === null || $city === '') {
            return null;
        }
        if (array_key_exists($city, self::benchmarks())) {
            return $city;
        }
        foreach (self::aliases() as $alias => $canonical) {
            if (mb_strtolower($alias, 'UTF-8') === mb_strtolower($city, 'UTF-8')) {
                return $canonical;
            }
        }

        return $city;
    }

    /**
     * Toon een herkenbare plaatsnaam (Bussum, Den Bosch) i.p.v. alleen de fusiegemeente
     * (Gooise Meren, 's-Hertogenbosch) wanneer URL of tekst die naam bevat.
     *
     * @param  string  $canonicalCity  Sleutel uit benchmarks() (output van locatie-detectie).
     */
    public static function displayPlaceLabel(string $canonicalCity, ?string $text, ?string $url): string
    {
        $aliases = [];
        foreach (self::aliases() as $alias => $canonical) {
            if ($canonical === $canonicalCity) {
                $aliases[] = $alias;
            }
        }
        if ($aliases === []) {
            return $canonicalCity;
        }

        usort($aliases, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        $hayUrl = $url !== null && $url !== ''
            ? mb_strtolower(rawurldecode($url), 'UTF-8')
            : '';
        $hayText = $text !== null && $text !== ''
            ? mb_strtolower($text, 'UTF-8')
            : '';

        foreach ($aliases as $alias) {
            $a = mb_strtolower($alias, 'UTF-8');
            if ($hayUrl !== '' && preg_match('/(?<![\p{L}\p{N}])'.preg_quote($a, '/').'(?![\p{L}\p{N}])/u', $hayUrl)) {
                return $alias;
            }
        }
        foreach ($aliases as $alias) {
            $a = mb_strtolower($alias, 'UTF-8');
            if ($hayText !== '' && preg_match('/(?<![\p{L}\p{N}])'.preg_quote($a, '/').'(?![\p{L}\p{N}])/u', $hayText)) {
                return $alias;
            }
        }

        return $canonicalCity;
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
