<?php

namespace App\Services;

class AiAnalysisService
{
    /**
     * @param  array{score: int, flags: array<int, string>}  $ruleScam
     */
    public function summarize(array $ruleScam): string
    {
        $parts = [];
        if ($ruleScam['score'] >= 61) {
            $parts[] = 'De analyse wijst op een verhoogd risico op fraude.';
        } elseif ($ruleScam['score'] >= 31) {
            $parts[] = 'Er zijn enkele signalen die voorzichtigheid adviseren.';
        } else {
            $parts[] = 'Er zijn beperkt aanwijzingen voor verhoogd scam-risico.';
        }

        if ($ruleScam['flags'] !== []) {
            $parts[] = 'Let op: '.implode('; ', $ruleScam['flags']).'.';
        } else {
            $parts[] = 'Er zijn geen automatisch gedetecteerde rode vlaggen.';
        }

        $parts[] = 'Dit is een samenvatting op basis van regels; het vervangt geen eigen oordeel.';

        return implode(' ', $parts);
    }
}
