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
            $parts[] = __('ai.summary_high');
        } elseif ($ruleScam['score'] >= 31) {
            $parts[] = __('ai.summary_mid');
        } else {
            $parts[] = __('ai.summary_low');
        }

        if ($ruleScam['flags'] !== []) {
            $parts[] = __('ai.summary_flags', ['flags' => implode('; ', $ruleScam['flags'])]);
        } else {
            $parts[] = __('ai.summary_no_flags');
        }

        $parts[] = __('ai.summary_footer');

        return implode(' ', $parts);
    }
}
