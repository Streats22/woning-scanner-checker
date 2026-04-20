<?php

namespace App\Services;

use App\Support\RentBenchmarkMap;

class LocationService
{
    /**
     * Detecteert de meest waarschijnlijke stad voor de benchmark.
     *
     * Volgorde:
     * 1) Gemeente in het pad van de bron-URL (huur/alkmaar/…) — sterk signaal bij gedeelde links.
     * 2) Tekst: hele woorden, nav-prefix overslaan, footer-downrank als er eerder een advertentie-context is.
     *
     * Aliassen (bijv. Den Bosch) worden naar de canonieke sleutel in RentBenchmarkMap opgelost.
     */
    public function detectCity(string $text, ?string $sourceUrl = null): ?string
    {
        $fromUrl = $this->cityFromListingUrl($sourceUrl);
        if ($fromUrl !== null) {
            return $fromUrl;
        }

        return $this->detectCityFromText($text);
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

    private function detectCityFromText(string $text): ?string
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

        usort($matches, fn ($a, $b) => $a['pos'] <=> $b['pos']);

        $skipPrefix = $this->boilerplateSkipLength($text);

        $afterNav = array_values(array_filter($matches, fn ($m) => $m['pos'] >= $skipPrefix));

        if ($afterNav !== []) {
            usort($afterNav, fn ($a, $b) => $a['pos'] <=> $b['pos']);

            return $afterNav[0]['city'];
        }

        $firstByCity = [];
        foreach ($matches as $m) {
            $c = $m['city'];
            if (! isset($firstByCity[$c]) || $m['pos'] < $firstByCity[$c]) {
                $firstByCity[$c] = $m['pos'];
            }
        }

        if (count($firstByCity) > 1) {
            arsort($firstByCity);

            return array_key_first($firstByCity);
        }

        return array_key_first($firstByCity);
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
     *
     * De skip mag niet te groot worden: anders valt de echte plaats in de titel (boven €/huur)
     * buiten de "after nav"-zone en wint een willekeurige stad uit de footer.
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
