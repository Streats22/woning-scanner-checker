<?php

namespace App\Services;

/**
 * Best-effort straat + huisnummer uit vrije advertentietekst (NL).
 * Vermijdt UI-teksten (bijv. "Aan / add to favorite") door suffixen en filters.
 */
class ListingAddressParser
{
    private const STREET_SUFFIX_PATTERN = 'straat|weg|laan|plantsoen|plein|dijk|singel|kade|gracht|hof|pad|baan|boulevard|steeg|markt|zoom|brug|allee|oever|terras|wijk';

    /**
     * @return array{street: ?string, number: ?string}
     */
    public function parseStreetAndNumber(?string $description): array
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
                '#\b([A-Za-zÀ-ÿ][A-Za-zÀ-ÿ0-9\.\s\'\-]{0,55}(?:'.self::STREET_SUFFIX_PATTERN.'))\s+(\d{1,5}[a-zA-Z]?)\s*$#u',
                $line,
                $m
            )) {
                $street = trim(preg_replace('/\s+/u', ' ', $m[1]) ?? '');
                $number = strtolower($m[2]);

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
        if (preg_match('/€|EUR|euro|per\s+maand|p\.m\.|pm\b/i', $line)) {
            return true;
        }

        if (preg_match('/\b(add\s+to|favorites?|favourites?|favoriet|favorieten|delen|share|cookie|cookies|nieuwsbrief|inschrijven|aanmelden|inloggen|menu|zoeken|filter)\b/i', $line)) {
            return true;
        }

        // Korte menuregel: "X / Y / Z" zonder echte straat-suffix in de regel
        if (substr_count($line, ' / ') >= 1
            && mb_strlen($line) < 120
            && ! preg_match('#(?:'.self::STREET_SUFFIX_PATTERN.')#i', $line)) {
            return true;
        }

        return false;
    }

    private function isStreetCandidateNoise(string $street): bool
    {
        if (preg_match('#favorite|favourites?|favoriet|delen|^aan\s*$|add\s+to|^\s*/\s*$#i', $street)) {
            return true;
        }

        // Alleen voegwoorden / ruis zonder straat-suffix (suffix zit al in pattern, dit vangt restjes)
        if (preg_match('/^(aan|de|het|een|van|te|in|op|bij|met|voor|naar)\s*$/u', trim($street))) {
            return true;
        }

        // Te veel leestekens / URL-achtig (geen echte straatregel)
        if (substr_count($street, '/') >= 1 && ! preg_match('#(?:'.self::STREET_SUFFIX_PATTERN.')#i', $street)) {
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

        if (preg_match('/^\d{3,5}$/', $number) && preg_match('/huur|prijs|€|kosten/i', $street)) {
            return true;
        }

        return false;
    }
}
