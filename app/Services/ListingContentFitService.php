<?php

namespace App\Services;

use App\Data\ParsedListingInput;

/**
 * Heuristiek: lijkt de ingelezen tekst op een huur-/woningadvertentie, of op willekeurige webpagina-boilerplate?
 */
class ListingContentFitService
{
    /**
     * @return array{tier: string, score: int, reason_codes: array<int, string>}
     */
    public function assess(ParsedListingInput $data): array
    {
        $text = $data->description;
        $url = $data->sourceUrl;

        $lower = mb_strtolower($text);
        $collapsed = trim(preg_replace('/\s+/u', ' ', $text) ?? '');
        $len = mb_strlen($collapsed);

        $score = 46;
        $codes = [];

        if ($len < 70) {
            $score -= 30;
            $codes[] = 'sparse_text';
        } elseif ($len < 200) {
            $score -= 14;
            $codes[] = 'short_text';
        }

        if ($data->price !== null) {
            $score += 20;
            $codes[] = 'has_price';
        }

        if ($data->contact !== null) {
            $score += 7;
            $codes[] = 'contact_hint';
        }

        $housingNl = [
            'te huur', 'huur', 'huurprijs', 'huur per', 'maandhuur', 'verhuur',
            'kamer', 'kamers', 'studio', 'appartement', 'woning',
            'slaapkamer', 'flat', 'penthouse', 'duplex',
            'm²', 'vierkante meter', 'woonoppervlak',
            'woonhuis', 'huurwoning', 'studentenkamer', 'kamer te huur',
            'borg', 'waarborgsom', 'servicekosten', 'inclusief',
            'exclusief', 'energielabel', 'bezichtiging', 'beschikbaar per',
        ];

        $housingEn = [
            'for rent', 'to rent', 'rent per', 'rental', 'bedroom', 'bathroom',
            'apartment', 'flat', 'per month', 'monthly rent', 'square meter', 'sqm',
        ];

        $termHits = 0;
        foreach (array_merge($housingNl, $housingEn) as $w) {
            if (str_contains($lower, mb_strtolower($w))) {
                $termHits++;
            }
        }

        if ($termHits >= 5) {
            $score += 24;
            $codes[] = 'rich_housing_vocab';
        } elseif ($termHits >= 3) {
            $score += 16;
            $codes[] = 'housing_vocab';
        } elseif ($termHits >= 1) {
            $score += 8;
            $codes[] = 'some_housing_vocab';
        }

        if (preg_match('/\b[1-9]\d{3}\s?[a-z]{2}\b/i', $text)) {
            $score += 9;
            $codes[] = 'postcode';
        }

        if (preg_match('/€\s*\d/', $text)) {
            $score += 5;
            $codes[] = 'euro_in_text';
        }

        if (preg_match('/\b\d{1,4}\s*m(?:2|²)\b|\d{1,4}\s*m2\b/ui', $text)) {
            $score += 6;
            $codes[] = 'surface_area';
        }

        $negative = [
            '404', 'page not found', 'pagina niet gevonden', 'pagina bestaat niet',
            'geen toegang', 'access denied', 'something went wrong',
            'oops, er ging iets mis', 'error 403', 'error 500',
        ];

        foreach ($negative as $n) {
            if (str_contains($lower, $n)) {
                $score -= 32;
                $codes[] = 'error_or_stub_page';
                break;
            }
        }

        if ($url !== null) {
            $host = mb_strtolower((string) parse_url($url, PHP_URL_HOST));
            if ($host !== '') {
                $platforms = [
                    'pararius.nl', 'funda.nl', 'kamernet.nl', 'huurwoningin.nl',
                    'huurzone.nl', '123wonen.nl', 'woninghuren.nl', 'rentola.nl',
                    'nestpick.com', 'housinganywhere.com',
                ];

                foreach ($platforms as $p) {
                    if ($host === $p || str_ends_with($host, '.'.$p)) {
                        $score += 16;
                        $codes[] = 'known_rental_platform';
                        break;
                    }
                }

                if (str_contains($host, 'marktplaats')) {
                    $score += 10;
                    $codes[] = 'classifieds_marktplaats';
                }

                if (str_contains($host, 'facebook.com')) {
                    $score += 6;
                    $codes[] = 'social_marketplace_hint';
                }
            }
        }

        $score = max(0, min(100, $score));
        $uniqueCodes = array_values(array_unique($codes));

        $tier = match (true) {
            $score >= 68 => 'strong',
            $score >= 42 => 'mixed',
            default => 'weak',
        };

        return [
            'tier' => $tier,
            'score' => $score,
            'reason_codes' => $uniqueCodes,
        ];
    }
}
