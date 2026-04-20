<?php

namespace App\Services;

use App\Support\RentBenchmarkMap;

class LocationService
{
    /**
     * Grote steden die vaak in navigatie, filters of voorbeeldadressen staan (", Utrecht", "Ook in Amsterdam").
     * Als de URL wél een andere (vaak kleinere) gemeente aangeeft — o.a. kamer-bussum — is dat meestal
     * betrouwbaarder dan een sterke tekstmatch op zo'n hub.
     *
     * @var list<string>
     */
    private const HUB_BENCHMARK_CITIES = [
        'Amsterdam',
        'Rotterdam',
        "'s-Gravenhage",
        'Utrecht',
        'Eindhoven',
        'Groningen',
    ];

    /**
     * Detecteert de meest waarschijnlijke stad voor de benchmark.
     *
     * Volgorde:
     * 1) Gemeente in het pad van de bron-URL (huur/alkmaar/…) — tenzij de tekst een veel
     *    sterker signaal geeft (bijv. "Straat, Plaats" met komma vóór de plaatsnaam in de
     *    advertentie-body, niet in navigatie met “, Utrecht”-achtige patronen).
     *    Ook: als de URL een niet-hubgemeente geeft (bijv. kamer-bussum → Gooise Meren) maar de
     *    tekst een sterke match op een veelvoorkomende hub (Utrecht, Amsterdam, …) — dan wint de URL,
     *    zodat voorbeeldadressen / filters in HTML de benchmark niet overschrijven.
     * 2) Tekst: hele woorden + score (komma-adres, advertentie-context, nav-downrank).
     *
     * Aliassen (bijv. Den Bosch, Bussum → Gooise Meren) worden naar de canonieke sleutel opgelost.
     */
    public function detectCity(string $text, ?string $sourceUrl = null): ?string
    {
        $fromUrl = $this->cityFromListingUrl($sourceUrl);
        $textResult = $this->detectCityFromTextResult($text);

        if ($fromUrl === null) {
            return $textResult['city'] ?? null;
        }
        if ($textResult === null) {
            return $fromUrl;
        }
        if ($fromUrl === $textResult['city']) {
            return $fromUrl;
        }

        /*
         * Tekst wint bij duidelijk adres (…, plaats) — o.a. regionale URL met verkeerde filterstad.
         * Een match met score ≥ 95 kan ook uit navigatie komen (bijv. “…, Utrecht” in de header);
         * dat is geen betrouwbaarder signaal dan een expliciete plaats in de URL (kamer-bussum/…).
         */
        if ($textResult['score'] >= 95) {
            $skipPrefix = $this->boilerplateSkipLength($text);
            if ($textResult['pos'] < $skipPrefix) {
                return $fromUrl;
            }

            if ($this->shouldPreferUrlOverHubTextCity($fromUrl, $textResult['city'])) {
                return $fromUrl;
            }

            return $textResult['city'];
        }

        return $fromUrl;
    }

    /**
     * URL wint als die een niet-hubgemeente aangeeft en de tekst vooral een "grote stad" matcht
     * (typisch ruis t.o.v. kamer-{plaats} in het pad, zie Kamernet).
     */
    private function shouldPreferUrlOverHubTextCity(string $fromUrlCity, string $textCity): bool
    {
        if ($fromUrlCity === $textCity) {
            return false;
        }

        if (! $this->isHubBenchmarkCity($textCity)) {
            return false;
        }

        return ! $this->isHubBenchmarkCity($fromUrlCity);
    }

    private function isHubBenchmarkCity(string $canonical): bool
    {
        foreach (self::HUB_BENCHMARK_CITIES as $hub) {
            if ($hub === $canonical) {
                return true;
            }
        }

        return false;
    }

    private function cityFromListingUrl(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);
        if ($path === null || $path === '' || $path === '/') {
            return null;
        }

        $segments = preg_split('#/+#', $path, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($segments as $segment) {
            $city = RentBenchmarkMap::canonicalFromPathSegment($segment);
            if ($city !== null) {
                return $city;
            }
        }

        return null;
    }

    /**
     * @return array{city: string, score: int, pos: int}|null
     */
    private function detectCityFromTextResult(string $text): ?array
    {
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        $needleToCanonical = RentBenchmarkMap::needleToCanonical();
        $matches = [];

        foreach (RentBenchmarkMap::searchNeedlesSorted() as $needle) {
            $canonical = $needleToCanonical[$needle] ?? $needle;
            $positions = $this->wholeWordMatchPositions($text, $needle);
            foreach ($positions as $pos) {
                $matches[] = ['city' => $canonical, 'pos' => $pos];
            }
        }

        if ($matches === []) {
            return null;
        }

        $matches = $this->filterLikelyFooterMatches($text, $matches);

        if ($matches === []) {
            return null;
        }

        $skipPrefix = $this->boilerplateSkipLength($text);

        $bestPerCity = [];
        foreach ($matches as $m) {
            $c = $m['city'];
            $s = $this->matchScore($text, $m['pos'], $skipPrefix);
            if (! isset($bestPerCity[$c]) || $s > $bestPerCity[$c]['score']) {
                $bestPerCity[$c] = ['city' => $c, 'score' => $s, 'pos' => $m['pos']];
            }
        }

        $candidates = array_values($bestPerCity);
        usort($candidates, function ($a, $b) {
            if ($a['score'] !== $b['score']) {
                return $b['score'] <=> $a['score'];
            }

            return $a['pos'] <=> $b['pos'];
        });

        $winner = $candidates[0];

        return ['city' => $winner['city'], 'score' => $winner['score'], 'pos' => $winner['pos']];
    }

    /**
     * Score per match: komma-regel (sterk), advertentie-context, downrank vroege boilerplate.
     */
    private function matchScore(string $text, int $bytePos, int $skipPrefix): int
    {
        $score = 0;
        if ($this->hasCommaBeforeCity($text, $bytePos)) {
            $score += 100;
        }
        if ($this->hasListingContextNearMatch($text, $bytePos)) {
            $score += 10;
        }
        if ($bytePos < $skipPrefix) {
            $score -= 15;
        }

        return $score;
    }

    /**
     * Typisch: "Veerplein, Bussum" — komma direct vóór de plaatsnaam.
     */
    private function hasCommaBeforeCity(string $text, int $bytePos): bool
    {
        if ($bytePos < 1) {
            return false;
        }

        $before = substr($text, max(0, $bytePos - 10), 10);

        return (bool) preg_match('/,\s*$/u', $before);
    }

    /**
     * @param  list<array{city: string, pos: int}>  $matches
     * @return list<array{city: string, pos: int}>
     */
    private function filterLikelyFooterMatches(string $text, array $matches): array
    {
        $lenBytes = strlen($text);
        if ($lenBytes < 2000) {
            return $matches;
        }

        $head = mb_substr($text, 0, 300, 'UTF-8');
        $headEndByte = strlen($head);
        $tailStart = max(0, $lenBytes - 700);

        $strongHeadCities = [];
        foreach ($matches as $m) {
            if ($m['pos'] >= $headEndByte) {
                continue;
            }
            if ($this->hasListingContextNearMatch($text, $m['pos'])) {
                $strongHeadCities[$m['city']] = true;
            }
        }

        if ($strongHeadCities === []) {
            return $matches;
        }

        return array_values(array_filter($matches, function ($m) use ($tailStart, $strongHeadCities) {
            if ($m['pos'] < $tailStart) {
                return true;
            }

            foreach (array_keys($strongHeadCities) as $headCity) {
                if ($headCity !== $m['city']) {
                    return false;
                }
            }

            return true;
        }));
    }

    /**
     * Advertentie-context nabij de match (€, huurtermen) — onderscheidt titel van losse footer-links.
     */
    private function hasListingContextNearMatch(string $text, int $bytePos): bool
    {
        $start = max(0, $bytePos - 140);
        $chunk = substr($text, $start, 360);

        return (bool) preg_match(
            '/€|EUR|euro|te\s+huur|huurprijs|per\s+maand|p\.m\.|kamer|appartement|woning|studio|maisonnette|huur\s*€|\d{3,4}\s*(?:€|EUR|euro)/iu',
            $chunk
        );
    }

    /**
     * @return list<int> byte-offsets (compatible with preg_match PREG_OFFSET_CAPTURE)
     */
    private function wholeWordMatchPositions(string $text, string $city): array
    {
        $pattern = '/(?<![\p{L}\p{N}])'.preg_quote($city, '/').'(?![\p{L}\p{N}])/ui';
        if (! preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE)) {
            return [];
        }

        return array_map(fn ($m) => $m[1], $matches[0]);
    }

    /**
     * Eerste regels van een lange geëxporteerde HTML-pagina bevatten vaak navigatie
     * ("Amsterdam" als voorbeeldstad); de echte locatie staat verderop. Bij korte
     * teksten (handmatig geplakt) slaan we niets over.
     */
    private function boilerplateSkipLength(string $text): int
    {
        $len = mb_strlen($text);
        if ($len <= 120) {
            return 0;
        }

        $skip = (int) min(400, max(200, floor($len * 0.08)));

        return min($skip, max(0, $len - 1));
    }
}
