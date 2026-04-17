<?php

namespace App\Services;

class LocationService
{
    /**
     * Steden waarvoor we een huurbenchmark hebben (zie PriceAnalysisService).
     *
     * @var list<string>
     */
    private const CITIES = ['Amsterdam', 'Rotterdam', 'Utrecht', 'Alkmaar'];

    /**
     * Detecteert de meest waarschijnlijke stad in de advertentietekst.
     *
     * Eerder werd de eerste match uit een vaste volgorde genomen; daardoor won
     * "Amsterdam" uit navigatie/footer vaak van "Rotterdam" in de advertentie.
     * We matchen nu hele woorden en negeren een korte prefix (header/nav), tenzij
     * daarbuiten geen stad meer voorkomt.
     */
    public function detectCity(string $text): ?string
    {
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        $matches = [];
        foreach (self::CITIES as $city) {
            $positions = $this->wholeWordMatchPositions($text, $city);
            foreach ($positions as $pos) {
                $matches[] = ['city' => $city, 'pos' => $pos];
            }
        }

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

        // Alles zat in de "nav"-prefix: kies per stad de eerste positie en neem de stad
        // waarvan die positie het grootst is (vaak de echte advertentie na boilerplate).
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

        $skip = (int) min(600, max(250, floor($len * 0.12)));

        return min($skip, max(0, $len - 1));
    }
}
