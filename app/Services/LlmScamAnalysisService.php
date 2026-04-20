<?php

namespace App\Services;

use App\Data\ParsedListingInput;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LlmScamAnalysisService
{
    public function __construct(
        private AiAnalysisService $ruleSummary,
        private ReportEnrichmentService $reportEnrichment,
        private ListingDwellingClassifier $dwellingClassifier,
    ) {}

    /**
     * Combineert regel-gebaseerde score met een LLM-analyse (alleen als $useLlm true en API-key gezet).
     * Gebruikt {@see app()} locale voor NL/EN prompts en output (zet locale vóór aanroep in de controller).
     *
     * @param  array{average: int, difference_percent: ?int}  $market
     * @param  array{score: int, flags: array<int, string>, breakdown: array<int, array{category: string, points: int, detail: string}>}  $ruleScam
     * @return array<string, mixed>
     */
    public function enhance(ParsedListingInput $data, array $market, array $ruleScam, bool $useLlm = true): array
    {
        if (! $useLlm) {
            return $this->fallbackFromRules($data, $market, $ruleScam);
        }

        $key = config('services.openai.key');
        if (! is_string($key) || $key === '') {
            return $this->fallbackFromRules($data, $market, $ruleScam);
        }

        $description = mb_substr($data->description, 0, 14000);
        $url = $data->sourceUrl ?? __('llm.no_url_label');

        $rawMax = config('services.openai.max_tokens');
        $maxTokens = is_numeric($rawMax) ? (int) $rawMax : 4096;
        $maxTokens = max(512, min(16384, $maxTokens));

        $userPayload = app()->getLocale() === 'en'
            ? [
                'source_url' => $url,
                'extracted_price' => $data->price,
                'contact_hint' => $data->contact,
                'dwelling_classification' => $this->dwellingClassifier->classify($data),
                'market_average_benchmark' => $market['average'],
                'market_difference_percent' => $market['difference_percent'],
                'rule_score' => $ruleScam['score'],
                'rule_flags' => $ruleScam['flags'],
                'rule_breakdown' => $ruleScam['breakdown'],
                'listing_text' => $description,
            ]
            : [
                'bron_url' => $url,
                'prijs_geëxtraheerd' => $data->price,
                'contact_hint' => $data->contact,
                'dwelling_classificatie' => $this->dwellingClassifier->classify($data),
                'markt_gemiddelde_richtprijs' => $market['average'],
                'markt_verschil_pct' => $market['difference_percent'],
                'regel_score' => $ruleScam['score'],
                'regel_vlaggen' => $ruleScam['flags'],
                'regel_breakdown' => $ruleScam['breakdown'],
                'advertentietekst' => $description,
            ];

        $payload = [
            'model' => config('services.openai.model'),
            'temperature' => 0.25,
            'max_tokens' => $maxTokens,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->systemPrompt(),
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($userPayload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
                ],
            ],
        ];

        try {
            $base = config('services.openai.base_url');
            $response = Http::withToken($key)
                ->timeout(55)
                ->connectTimeout(15)
                ->acceptJson()
                ->post($base.'/chat/completions', $payload);

            if (! $response->successful()) {
                Log::warning('LLM scam analyse HTTP mislukt', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $this->fallbackFromRules($data, $market, $ruleScam);
            }

            $content = $response->json('choices.0.message.content');
            if (! is_string($content) || $content === '') {
                return $this->fallbackFromRules($data, $market, $ruleScam);
            }

            /** @var array<string, mixed> $parsed */
            $parsed = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            $aiScore = isset($parsed['score']) ? (int) $parsed['score'] : 0;
            $aiScore = max(0, min(100, $aiScore));

            $aiFlags = [];
            if (isset($parsed['flags']) && is_array($parsed['flags'])) {
                foreach ($parsed['flags'] as $f) {
                    if (is_string($f) && trim($f) !== '') {
                        $aiFlags[] = trim($f);
                    }
                }
            }

            $mergedFlags = array_values(array_unique([...$ruleScam['flags'], ...$aiFlags]));

            $finalScore = min(100, max($ruleScam['score'], $aiScore));

            $baseExtras = $this->reportEnrichment->buildRuleBasedExtras($data, $market, $ruleScam);

            $llmRec = $this->parseStringList($parsed['recommendations'] ?? null, 10);
            $llmVerify = $this->parseStringList($parsed['what_to_verify'] ?? null, 10);
            $observations = $this->parseStringList($parsed['observations'] ?? null, 8);

            $recommendations = array_values(array_unique([...$baseExtras['recommendations'], ...$llmRec]));
            $whatToVerify = array_values(array_unique([...$baseExtras['what_to_verify'], ...$llmVerify]));

            $narrative = isset($parsed['narrative']) && is_string($parsed['narrative']) && trim($parsed['narrative']) !== ''
                ? trim($parsed['narrative'])
                : null;

            $summaryShort = isset($parsed['summary']) && is_string($parsed['summary']) && trim($parsed['summary']) !== ''
                ? trim($parsed['summary'])
                : $this->ruleSummary->summarize(['score' => $finalScore, 'flags' => $mergedFlags]);

            $summary = $narrative !== null
                ? $summaryShort."\n\n".$narrative
                : $summaryShort;

            $linkAssessment = $this->formatLinkAssessment($data->sourceUrl, $parsed);

            $llmBreakdown = $this->parseRiskBreakdown($parsed['risk_breakdown'] ?? null);
            $riskBreakdown = $this->mergeBreakdowns($ruleScam['breakdown'], $llmBreakdown);

            return [
                'score' => $finalScore,
                'flags' => $mergedFlags,
                'summary' => $summary,
                'summary_short' => $summaryShort,
                'narrative' => $narrative,
                'observations' => $observations,
                'llm_used' => true,
                'link_assessment' => $linkAssessment,
                'recommendations' => $recommendations,
                'what_to_verify' => $whatToVerify,
                'risk_breakdown' => $riskBreakdown,
                'rule_score' => $ruleScam['score'],
                'methodology' => $baseExtras['methodology'],
                'market_context' => $baseExtras['market_context'],
            ];
        } catch (\Throwable $e) {
            Log::warning('LLM scam analyse mislukt', ['exception' => $e]);

            return $this->fallbackFromRules($data, $market, $ruleScam);
        }
    }

    private function systemPrompt(): string
    {
        return app()->getLocale() === 'en'
            ? $this->systemPromptEn()
            : $this->systemPromptNl();
    }

    private function systemPromptNl(): string
    {
        return <<<'PROMPT'
Je bent een Nederlandse expert in huur-/kamerverhuur-fraude en misleidende advertenties.
Je krijgt tekst (en soms een bron-URL) plus een eenvoudige regel-analyse (score, vlaggen, onderdelen) en een automatische schatting van type woning (kamer vs hele woning) en sector (particulier vs sociale huur vs onbekend).
De server herkent o.a.: onderprijs vs. benchmark, WhatsApp/Telegram/Signal/Skype, tijdsdruk (o.a. “veel interesse”), Western Union/crypto/cadeaukaarten, geen bezichtiging of buitenland-verhaal, sleutelservice, vroeg om ID/paspoort, voorafkosten, Google Forms/Typeform, copy-paste verhalen, Engelstalige sjablonen.
Gebruik de meegegeven dwelling_classificatie als hulp — het is geen juridische kwalificatie.

Taken:
1) Beoordeel of de inhoud consistent lijkt met een echte huuradvertentie of op scam/phishing wijst.
2) Als er een URL is: beoordeel inhoudelijk of de tekst past bij een woningadvertentie (geen technische URL-check).
3) Geef een risicoscore 0-100 en concrete rode vlaggen (korte zinnen).
4) Lever uitgebreide, praktische aanbevelingen en wat de lezer moet controleren vóór betaling.
5) Geef per risicocategorie punten (0-100 totaal niet overschrijven in de som — het zijn deelnemers aan het risico).
6) Combineer met de meegegeven regel-score: wees conservatief bij twijfel.

Antwoord ALLEEN met geldige JSON, dit schema:
{
  "score": number,
  "flags": string[],
  "summary": string,
  "narrative": string,
  "observations": string[],
  "link_data_quality": "consistent"|"twijfelachtig"|"onvoldoende_data"|null,
  "link_note": string|null,
  "recommendations": string[],
  "what_to_verify": string[],
  "risk_breakdown": [{"category": string, "points": number, "detail": string}]
}

- narrative: 5-10 zinnen Nederlands, diepgaander dan summary.
- summary: 2-3 zinnen kern.
- observations: 4-8 korte observaties (1 zin elk) over taal, structuur, inconsistenties, contactpatronen — niet herhalen van flags, wel extra detail.
- recommendations: minimaal 4, maximaal 10 concrete acties voor de huurder.
- what_to_verify: minimaal 4, maximaal 10 controle-stappen.
- link_data_quality: null als er geen URL was.
PROMPT;
    }

    private function systemPromptEn(): string
    {
        return <<<'PROMPT'
You are an expert in rental and room-rental fraud and misleading listings (Netherlands/EU context).
You receive text (and sometimes a source URL), a simple rule-based analysis (score, flags, breakdown), and an automatic estimate of dwelling type (room vs whole home) and sector (private vs social vs unknown).
The server detects e.g.: under-pricing vs benchmark, WhatsApp/Telegram/Signal/Skype, time pressure, Western Union/crypto/gift cards, no viewing or abroad story, key services, early ID requests, upfront fees, Google Forms/Typeform, copy-paste stories, English template phrases.
Use the supplied dwelling classification as a hint — it is not a legal classification.

Tasks:
1) Assess whether the content looks like a genuine rental listing or scam/phishing.
2) If a URL is present: assess whether the text fits a property listing in substance (no technical URL inspection).
3) Give a risk score 0-100 and concrete red flags (short lines).
4) Provide practical recommendations and what the reader should verify before paying.
5) Give points per risk category (do not let the sum exceed 100 across categories — they contribute to risk).
6) Combine with the given rule score: be conservative when unsure.

Reply ONLY with valid JSON, this schema:
{
  "score": number,
  "flags": string[],
  "summary": string,
  "narrative": string,
  "observations": string[],
  "link_data_quality": "consistent"|"doubtful"|"insufficient_data"|null,
  "link_note": string|null,
  "recommendations": string[],
  "what_to_verify": string[],
  "risk_breakdown": [{"category": string, "points": number, "detail": string}]
}

- narrative: 5-10 sentences in English, deeper than summary.
- summary: 2-3 sentence core.
- observations: 4-8 short observations (one sentence each) on language, structure, inconsistencies, contact patterns — do not repeat flags verbatim.
- recommendations: at least 4, at most 10 concrete renter actions.
- what_to_verify: at least 4, at most 10 verification steps.
- link_data_quality: null if there was no URL.
PROMPT;
    }

    /**
     * @param  array{score: int, flags: array<int, string>, breakdown: array<int, array{category: string, points: int, detail: string}>}  $ruleScam
     * @return array<string, mixed>
     */
    private function fallbackFromRules(ParsedListingInput $data, array $market, array $ruleScam): array
    {
        $extras = $this->reportEnrichment->buildRuleBasedExtras($data, $market, $ruleScam);
        $summary = $this->ruleSummary->summarize([
            'score' => $ruleScam['score'],
            'flags' => $ruleScam['flags'],
        ]);

        return [
            'score' => $ruleScam['score'],
            'flags' => $ruleScam['flags'],
            'summary' => $summary,
            'summary_short' => $summary,
            'narrative' => null,
            'observations' => [],
            'llm_used' => false,
            'link_assessment' => null,
            'recommendations' => $extras['recommendations'],
            'what_to_verify' => $extras['what_to_verify'],
            'risk_breakdown' => $ruleScam['breakdown'],
            'rule_score' => $ruleScam['score'],
            'methodology' => $extras['methodology'],
            'market_context' => $extras['market_context'],
        ];
    }

    /**
     * @param  array<int, array{category: string, points: int, detail: string}>  $a
     * @param  array<int, array{category: string, points: int, detail: string}>  $b
     * @return array<int, array{category: string, points: int, detail: string}>
     */
    private function mergeBreakdowns(array $a, array $b): array
    {
        if ($b === []) {
            return $a;
        }

        $byCat = [];
        foreach ($a as $row) {
            $byCat[$row['category']] = $row;
        }
        foreach ($b as $row) {
            $cat = $row['category'];
            if (! isset($byCat[$cat]) || $row['points'] > $byCat[$cat]['points']) {
                $byCat[$cat] = $row;
            }
        }

        return array_values($byCat);
    }

    /**
     * @return array<int, array{category: string, points: int, detail: string}>
     */
    private function parseRiskBreakdown(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $row) {
            if (! is_array($row)) {
                continue;
            }
            $cat = isset($row['category']) && is_string($row['category']) ? trim($row['category']) : '';
            $detail = isset($row['detail']) && is_string($row['detail']) ? trim($row['detail']) : '';
            $points = isset($row['points']) ? max(0, min(100, (int) $row['points'])) : 0;
            if ($cat === '' || $detail === '') {
                continue;
            }
            $out[] = ['category' => $cat, 'points' => $points, 'detail' => $detail];
        }

        return $out;
    }

    /**
     * @return array<int, string>
     */
    private function parseStringList(mixed $raw, int $max): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $item) {
            if (is_string($item) && trim($item) !== '') {
                $out[] = trim($item);
            }
            if (count($out) >= $max) {
                break;
            }
        }

        return $out;
    }

    /**
     * @param  array{summary?: mixed, link_data_quality?: mixed, link_note?: mixed}  $parsed
     */
    private function formatLinkAssessment(?string $sourceUrl, array $parsed): ?string
    {
        if ($sourceUrl === null || $sourceUrl === '') {
            return null;
        }

        $quality = $parsed['link_data_quality'] ?? null;
        $note = isset($parsed['link_note']) && is_string($parsed['link_note']) ? trim($parsed['link_note']) : '';

        $parts = [];
        if (is_string($quality) && $quality !== '') {
            $parts[] = __('llm.link_assessment_quality', [
                'quality' => $this->normalizeLinkQualityLabel($quality),
            ]);
        }
        if ($note !== '') {
            $parts[] = $note;
        }

        return $parts !== [] ? implode(' ', $parts) : null;
    }

    private function normalizeLinkQualityLabel(string $raw): string
    {
        $n = mb_strtolower(trim($raw));
        $key = match ($n) {
            'consistent' => 'consistent',
            'twijfelachtig', 'doubtful' => 'doubtful',
            'onvoldoende_data', 'insufficient_data' => 'insufficient',
            default => null,
        };

        if ($key === null) {
            return $raw;
        }

        return __('llm.quality.'.$key);
    }
}
