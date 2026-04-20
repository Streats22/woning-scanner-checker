<?php

namespace App\Services;

use App\Support\RentBenchmarkMap;

/**
 * Best-effort straat + huisnummer uit vrije advertentietekst (NL).
 * Vermijdt UI-teksten (bijv. "Aan / add to favorite") door suffixen en filters.
 * Als er geen straat in de tekst staat: fallback op URL-paden (bijv. directwonen.nl/…/coornhertkade/…).
 */
class ListingAddressParser
{
    private const STREET_SUFFIX_PATTERN = 'straat|weg|laan|plantsoen|plein|dijk|singel|kade|gracht|hof|pad|baan|boulevard|steeg|markt|zoom|brug|allee|oever|terras|wijk';

    /**
     * @return array{street: ?string, number: ?string}
     */
    public function parseStreetAndNumber(?string $description, ?string $sourceUrl = null): array
    {
        $fromText = $this->parseStreetAndNumberFromText($description);
        if ($fromText['street'] !== null) {
            return $fromText;
        }

        return $this->parseStreetFromListingUrl($sourceUrl);
    }

    /**
     * @return array{street: ?string, number: ?string}
     */
    private function parseStreetAndNumberFromText(?string $description): array
    {
        if ($description === null || trim($description) === '') {
            return ['street' => null, 'number' => null];
        }

        // 1) Zoek in de volledige tekst: echte NL-adressen eindigen op een straat-type + huisnummer.
        $fromFullText = $this->matchStreetWithSuffixInText($description);
        if ($fromFullText !== null) {
            return $fromFullText;
        }

        // 2) Regel-voor-regel (dieper in het document), met suffix-check — geen losse "woord + nr" meer.
        $lines = preg_split('/\R+/u', $description) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || mb_strlen($line) < 8) {
                continue;
            }

            if ($this->shouldSkipLineAsUiOrNoise($line)) {
                continue;
            }

            if (preg_match(
                '#\b([A-Za-zÀ-ÿ][A-Za-zÀ-ÿ0-9.\s\'\-]{0,55}('.self::STREET_SUFFIX_PATTERN.'))\s+(\d{1,5}[a-zA-Z]?)\s*$#u',
                $line,
                $m
            )) {
                $street = trim(preg_replace('/\s+/u', ' ', $m[1]) ?? '');
                $number = strtolower($m[3]);

                if ($this->looksLikeYearOrPrice($street, $number)) {
                    continue;
                }

                if ($this->isStreetCandidateNoise($street)) {
                    continue;
                }

                return ['street' => $street, 'number' => $number];
            }
        }

        return ['street' => null, 'number' => null];
    }

    /**
     * Veel woningsites zetten de straat als slug in het pad (na de gemeente), zonder dat die in de HTML-tekst staat.
     *
     * @return array{street: ?string, number: ?string}
     */
    private function parseStreetFromListingUrl(?string $url): array
    {
        if ($url === null || trim($url) === '') {
            return ['street' => null, 'number' => null];
        }

        $path = parse_url($url, PHP_URL_PATH);
        if ($path === null || $path === '' || $path === '/') {
            return ['street' => null, 'number' => null];
        }

        $segments = preg_split('#/+#', $path, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($segments as $seg) {
            $seg = rawurldecode(trim($seg));
            if ($seg === '' || mb_strlen($seg) < 5) {
                continue;
            }

            if (preg_match('/\d{5,}/', $seg)) {
                continue;
            }

            if (RentBenchmarkMap::canonicalFromPathSegment($seg) !== null) {
                continue;
            }

            if ($this->isUrlSegmentNoise($seg)) {
                continue;
            }

            if (preg_match('/^(appartement|woning|huis|studio|kamer|penthouse|bungalow|villa|loft|duplex)(-.+)?$/iu', $seg)) {
                continue;
            }

            $street = $this->humanizeStreetSlug($seg);
            if ($street !== null) {
                return ['street' => $street, 'number' => null];
            }
        }

        return ['street' => null, 'number' => null];
    }

    private function isUrlSegmentNoise(string $seg): bool
    {
        $low = mb_strtolower($seg, 'UTF-8');

        if (preg_match('/^(huurwoningen|huurwoning|huur\-?woningen|woningen)(-huren)?$/iu', $low)) {
            return true;
        }

        if (preg_match('/^(huur|huren|zoeken|search|nl|en|page|pg|pagina|results|listing|object|details|aanbod|zoek|finder|woning|te\-huur|for\-rent|rent|kopen|kopers)(-.+)?$/iu', $low)) {
            return true;
        }

        return (bool) preg_match('/^(huur|huren|zoek|search|nl|en)$/iu', $low);
    }

    private function humanizeStreetSlug(string $slug): ?string
    {
        $slug = mb_strtolower(trim($slug), 'UTF-8');
        if ($slug === '' || mb_strlen($slug) < 6) {
            return null;
        }

        $suffixes = explode('|', self::STREET_SUFFIX_PATTERN);
        usort($suffixes, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));
        $suffixAlt = implode('|', array_map(static fn ($s) => preg_quote($s, '/'), $suffixes));

        if (! preg_match('/^(.+)('.$suffixAlt.')$/iu', $slug, $m)) {
            return null;
        }

        $namePart = $m[1];
        $suffixLower = mb_strtolower($m[2], 'UTF-8');

        if ($namePart === '' || mb_strlen($namePart) < 2) {
            return null;
        }

        if (str_contains($namePart, '-')) {
            $words = explode('-', $namePart);
            $out = [];
            foreach ($words as $w) {
                $w = trim($w);
                if ($w === '') {
                    continue;
                }
                $lw = mb_strtolower($w, 'UTF-8');
                if (in_array($lw, ['van', 'de', 'het', 'den', 'ten', 'te', 'op', 'voor', 'aan', 'bij'], true)) {
                    $out[] = $lw;
                } else {
                    $out[] = mb_convert_case($w, MB_CASE_TITLE, 'UTF-8');
                }
            }

            return implode(' ', $out).$suffixLower;
        }

        return mb_convert_case($namePart.$suffixLower, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * @return array{street: string, number: string}|null
     */
    private function matchStreetWithSuffixInText(string $text): ?array
    {
        $pattern = '#\b([A-Za-zÀ-ÿ][A-Za-zÀ-ÿ0-9\.\s\'\-]{0,55}(?:'.self::STREET_SUFFIX_PATTERN.'))\s+(\d{1,5}[a-zA-Z]?)\b#u';

        if (! preg_match_all($pattern, $text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            return null;
        }

        foreach ($matches as $m) {
            $street = trim(preg_replace('/\s+/u', ' ', $m[1][0]) ?? '');
            $number = strtolower($m[2][0]);

            if ($this->looksLikeYearOrPrice($street, $number)) {
                continue;
            }

            if ($this->isStreetCandidateNoise($street)) {
                continue;
            }

            return ['street' => $street, 'number' => $number];
        }

        return null;
    }

    private function shouldSkipLineAsUiOrNoise(string $line): bool
    {
        if (preg_match('/€|EUR|euro|per\s+maand|p\.m\.|pm\b/iu', $line)) {
            return true;
        }

        if (preg_match('/\b(add\s+to|favorites?|favourites?|favoriet|favorieten|delen|share|cookie|cookies|nieuwsbrief|inschrijven|aanmelden|inloggen|menu|zoeken|filter)\b/iu', $line)) {
            return true;
        }

        // Korte menuregel: "X / Y / Z" zonder echte straat-suffix in de regel
        if (substr_count($line, ' / ') >= 1
            && mb_strlen($line) < 120
            && ! preg_match('#'.self::STREET_SUFFIX_PATTERN.'#iu', $line)) {
            return true;
        }

        return false;
    }

    private function isStreetCandidateNoise(string $street): bool
    {
        if (preg_match('#favorite|favourites?|favoriet|delen|^aan\s*$|add\s+to|^\s*/\s*$#iu', $street)) {
            return true;
        }

        // Alleen voegwoorden / ruis zonder straat-suffix (suffix zit al in pattern, dit vangt restjes)
        if (preg_match('/^(aan|de|het|een|van|te|in|op|bij|met|voor|naar)\s*$/u', trim($street))) {
            return true;
        }

        // Te veel leestekens / URL-achtig (geen echte straatregel)
        if (substr_count($street, '/') >= 1 && ! preg_match('#'.self::STREET_SUFFIX_PATTERN.'#iu', $street)) {
            return true;
        }

        // Geen letters over — alleen symboolresten
        if (! preg_match('/\p{L}/u', $street)) {
            return true;
        }

        return false;
    }

    private function looksLikeYearOrPrice(string $street, string $number): bool
    {
        if (preg_match('/^(19|20)\d{2}$/', $number)) {
            return true;
        }

        if (preg_match('/^\d{3,5}$/', $number) && preg_match('/huur|prijs|€|kosten/iu', $street)) {
            return true;
        }

        return false;
    }
}
