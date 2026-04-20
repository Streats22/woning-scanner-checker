<?php

namespace App\Services;

/**
 * Haalt woonoppervlakte (m²) uit advertentietekst — automatische schatting, geen garantie.
 */
final class ListingSurfaceParser
{
    private const MIN_M2 = 6.0;

    private const MAX_M2 = 600.0;

    public function parse(?string $text): ?float
    {
        if ($text === null || $text === '') {
            return null;
        }

        $context = $this->matchAfterKeywords($text);
        if ($context !== null) {
            return $context;
        }

        return $this->matchFirstPlausibleM2($text);
    }

    private function matchAfterKeywords(string $text): ?float
    {
        $re = '/(?:woonoppervlak(?:te)?|gebruiksoppervlak|huuroppervlak|oppervlakte(?:\s+wonen)?)\s*[:\-]?\s*(\d{1,3}(?:[,.]\d{1,2})?)\s*m[²2]\b/iu';

        if (preg_match($re, $text, $m)) {
            return $this->normalizeAndValidate($m[1]);
        }

        return null;
    }

    private function matchFirstPlausibleM2(string $text): ?float
    {
        if (! preg_match_all('/\b(\d{1,3}(?:[,.]\d{1,2})?)\s*m[²2]\b/iu', $text, $matches, PREG_SET_ORDER)) {
            return null;
        }

        foreach ($matches as $m) {
            $v = $this->normalizeAndValidate($m[1]);
            if ($v !== null) {
                return $v;
            }
        }

        return null;
    }

    private function normalizeAndValidate(string $raw): ?float
    {
        $s = str_replace(',', '.', trim($raw));
        if (! is_numeric($s)) {
            return null;
        }

        $v = (float) $s;
        if ($v < self::MIN_M2 || $v > self::MAX_M2) {
            return null;
        }

        return round($v, 1);
    }
}
