<?php

namespace App\Services;

use App\Data\ParsedListingInput;

/**
 * Heuristische classificatie: kamer vs hele woning, en particuliere vs sociale huur.
 * Patronen sluiten aan bij gangbare NL-termen (o.a. Woonbond/Rijksoverheid/Huurcommissie — zie FAQ-bronnen).
 * Geen juridische kwalificatie; alleen tekst- en URL-signalen.
 *
 * Zelfde invoer in één request wordt gecached (DRY: één keer scoren voor enrich + facts + LLM-payload).
 */
final class ListingDwellingClassifier
{
    /** @var array<string, array<string, mixed>> */
    private array $cache = [];

    /**
     * @return array{
     *     kind: 'room'|'whole'|'unknown',
     *     kind_confidence: 'high'|'medium'|'low',
     *     rental_sector: 'private'|'social'|'unknown',
     *     sector_confidence: 'high'|'medium'|'low',
     *     signals: array<int, string>
     * }
     */
    public function classify(ParsedListingInput $input): array
    {
        $key = hash('xxh128', ($input->sourceUrl ?? '')."\0".$input->description);

        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        return $this->cache[$key] = $this->classifyUncached($input);
    }

    /**
     * @return array{
     *     kind: 'room'|'whole'|'unknown',
     *     kind_confidence: 'high'|'medium'|'low',
     *     rental_sector: 'private'|'social'|'unknown',
     *     sector_confidence: 'high'|'medium'|'low',
     *     signals: array<int, string>
     * }
     */
    private function classifyUncached(ParsedListingInput $input): array
    {
        $text = $input->description;
        $url = $input->sourceUrl ?? '';
        $hay = mb_strtolower($text);
        $urlHay = mb_strtolower($url);

        $signals = [];

        $roomScore = 0;

        if (preg_match('/studentenkamer|\b(?:studenten)?kamer\b/u', $hay)) {
            $roomScore += 2;
            $signals[] = 'kamer';
        }

        if (preg_match('/kamer\s+te\s+huur|te\s+huur:?\s*[^.]{0,40}\bkamer\b|huur\s+kamer|per\s+kamer|kamer\s+in\s+|kamer\s+beschikbaar/u', $hay)) {
            $roomScore += 3;
            $signals[] = 'kamer-te-huur-context';
        }

        if (preg_match('/huisgenoot|hospita|woningdeel|medebewoner|flatmate|shared\s+(?:flat|house|apartment)|room\s+for\s+rent/u', $hay)) {
            $roomScore += 3;
            $signals[] = 'huisgenoot-gedeeld';
        }

        if (preg_match('/\d[\s\-]*(?:slaapkamer|kamer|kamers)\b.*(?:appartement|maisonnette|woning|huis)/u', $hay)) {
            $roomScore -= 4;
            $signals[] = 'meerdere-kamers-woning';
        }

        if (preg_match('/\b(?:tussenwoning|hoekwoning|eengezinswoning|vrijstaand|penthouse|duplex|maisonnette)\b/u', $hay)) {
            $roomScore -= 2;
        }

        if (preg_match('#/(?:kamer|kamers|studentenkamer)(?:/|$)#u', $urlHay)) {
            $roomScore += 2;
            $signals[] = 'url-kamer';
        }

        $wholeBoost = 0;
        if (preg_match('/\b(?:appartement|maisonnette|studio|loft|woonhuis|woning|penthouse|duplex)\b/u', $hay)) {
            $wholeBoost++;
        }

        $kind = 'unknown';
        if ($roomScore >= 3) {
            $kind = 'room';
        } elseif ($roomScore <= -1 && $wholeBoost > 0) {
            $kind = 'whole';
        } elseif ($wholeBoost > 0 && $roomScore < 2) {
            $kind = 'whole';
        } elseif (preg_match('/\bstudio\b/u', $hay) && $roomScore < 2) {
            $kind = 'whole';
        }

        $kindConfidence = 'low';
        if ($kind !== 'unknown') {
            $abs = abs($roomScore);
            $kindConfidence = $abs >= 4 ? 'high' : ($abs >= 2 ? 'medium' : 'low');
        }

        $socialScore = 0;
        $pairs = [
            '/sociale\s+huur|niet\s+free\s*sector|niet-vrije\s*sector/u' => 4,
            '/woningwaarderingsstelsel|\bwws\b|woningpunten|maximale\s+huur|huurprijs\s+binnen\s+de\s+grenzen/u' => 3,
            '/woningcorporatie|huurcorporatie|verhuurd\s+door\s+corporatie/u' => 3,
            '/\b(?:woonnet|huisvesting\s+match|roomspot|thuisborg)\b/u' => 2,
            '/gemeente.*(?:huisvesting|woning|inschrijving|urgentie)|huisvestingsverordening/u' => 2,
            '/toewijzing\s+(?:van\s+)?(?:een\s+)?woning|woning\s+toegewezen|wachtlijst\s+(?:corporatie|woning)/u' => 2,
            '/recht\s+op\s+huurtoeslag.*(?:woning|huur)|huurtoeslag.*(?:sociale|niet)/u' => 2,
        ];
        foreach ($pairs as $re => $w) {
            if (preg_match($re, $hay)) {
                $socialScore += $w;
            }
        }
        if (preg_match('/\.(?:woonnet|huisvesting)[^.\s]*/u', $urlHay)) {
            $socialScore += 2;
            $signals[] = 'url-woonnet-achtig';
        }

        $privateScore = 0;
        if (preg_match('/\bparticulier|particuliere\s+verhuur|direct\s+bij\s+de\s+eigenaar|eigen\s+woning\s+verhuur/u', $hay)) {
            $privateScore += 4;
            $signals[] = 'particulier';
        }
        if (preg_match('/\bvrije\s+sector\b/u', $hay)) {
            $privateScore += 3;
            $signals[] = 'vrije-sector';
        }
        if (preg_match('/\b(?:makelaar|nvm|verhuurmakelaar|aanhuurmakelaar)\b/u', $hay)) {
            $privateScore += 2;
        }

        $sector = 'unknown';
        if ($socialScore >= 4 && $socialScore > $privateScore) {
            $sector = 'social';
        } elseif ($privateScore >= 3 && $privateScore > $socialScore) {
            $sector = 'private';
        } elseif ($socialScore >= 3 && $privateScore === 0) {
            $sector = 'social';
        } elseif ($privateScore >= 2 && $socialScore === 0) {
            $sector = 'private';
        }

        $sectorConfidence = 'low';
        if ($sector !== 'unknown') {
            $lead = $sector === 'social' ? $socialScore : $privateScore;
            $sectorConfidence = $lead >= 5 ? 'high' : ($lead >= 3 ? 'medium' : 'low');
        }

        // Losse kamers op de tweedehands markt zijn vrijwel altijd particuliere (vrije sector) verhuur;
        // sociale huur-kamers zijn zeldzaam en hebben meestal expliciete corporatie-/woonnet-signalen (hierboven).
        if ($kind === 'room' && $sector === 'unknown') {
            $sector = 'private';
            $sectorConfidence = 'medium';
            $signals[] = 'kamer-particulier';
        }

        $signals = array_values(array_unique(array_slice($signals, 0, 6)));

        return [
            'kind' => $kind,
            'kind_confidence' => $kindConfidence,
            'rental_sector' => $sector,
            'sector_confidence' => $sectorConfidence,
            'signals' => $signals,
        ];
    }
}
