<?php

namespace Tests\Unit;

use App\Data\ParsedListingInput;
use App\Services\AiAnalysisService;
use App\Services\LlmScamAnalysisService;
use App\Services\ReportEnrichmentService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LlmScamAnalysisServiceTest extends TestCase
{
    public function test_fallback_without_api_key(): void
    {
        Config::set('services.openai.key', '');

        $service = new LlmScamAnalysisService(new AiAnalysisService, new ReportEnrichmentService);
        $data = new ParsedListingInput(null, 500, null, 'Test Amsterdam');
        $market = ['average' => 1800, 'difference_percent' => -72];
        $rule = ['score' => 30, 'flags' => ['Prijs'], 'breakdown' => []];

        $out = $service->enhance($data, $market, $rule);

        $this->assertFalse($out['llm_used']);
        $this->assertSame(30, $out['score']);
        $this->assertSame(['Prijs'], $out['flags']);
        $this->assertNull($out['link_assessment']);
        $this->assertNotEmpty($out['recommendations']);
        $this->assertNotEmpty($out['what_to_verify']);
    }

    public function test_llm_merges_with_rules_when_openai_returns_json(): void
    {
        Config::set('services.openai.key', 'sk-test');
        Config::set('services.openai.model', 'gpt-4o-mini');
        Config::set('services.openai.base_url', 'https://api.openai.com/v1');

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'score' => 55,
                                'flags' => ['Extra AI vlag'],
                                'summary' => 'Samenvatting van AI.',
                                'narrative' => 'Langere toelichting.',
                                'link_data_quality' => null,
                                'link_note' => null,
                                'recommendations' => ['Tip A'],
                                'what_to_verify' => ['Check B'],
                                'risk_breakdown' => [['category' => 'Test', 'points' => 10, 'detail' => 'X']],
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = new LlmScamAnalysisService(new AiAnalysisService, new ReportEnrichmentService);
        $data = new ParsedListingInput(null, 500, null, str_repeat('x', 200));
        $market = ['average' => 1800, 'difference_percent' => -72];
        $rule = ['score' => 30, 'flags' => ['Prijs'], 'breakdown' => []];

        $out = $service->enhance($data, $market, $rule);

        $this->assertTrue($out['llm_used']);
        $this->assertSame(55, $out['score']);
        $this->assertContains('Extra AI vlag', $out['flags']);
        $this->assertContains('Prijs', $out['flags']);
        $this->assertStringContainsString('Samenvatting van AI.', $out['summary']);
        $this->assertStringContainsString('Langere toelichting.', $out['summary']);
    }

    public function test_link_assessment_when_url_present(): void
    {
        Config::set('services.openai.key', 'sk-test');
        Config::set('services.openai.model', 'gpt-4o-mini');
        Config::set('services.openai.base_url', 'https://api.openai.com/v1');

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'score' => 20,
                                'flags' => [],
                                'summary' => 'Ok.',
                                'narrative' => '',
                                'recommendations' => [],
                                'what_to_verify' => [],
                                'risk_breakdown' => [],
                                'link_data_quality' => 'twijfelachtig',
                                'link_note' => 'Weinig woningdetails.',
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = new LlmScamAnalysisService(new AiAnalysisService, new ReportEnrichmentService);
        $data = new ParsedListingInput('https://example.com/ad', 800, null, str_repeat('y', 200));
        $market = ['average' => 1300, 'difference_percent' => -38];
        $rule = ['score' => 10, 'flags' => [], 'breakdown' => []];

        $out = $service->enhance($data, $market, $rule);

        $this->assertTrue($out['llm_used']);
        $this->assertStringContainsString('twijfelachtig', (string) $out['link_assessment']);
        $this->assertStringContainsString('Weinig woningdetails', (string) $out['link_assessment']);
    }
}
