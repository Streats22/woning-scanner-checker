<?php

namespace Tests\Unit;

use App\Data\ParsedListingInput;
use App\Services\LlmScamAnalysisService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LlmScamAnalysisServiceTest extends TestCase
{
    public function test_fallback_without_api_key(): void
    {
        Config::set('services.openai.key', '');
        Config::set('services.openrouter.key', '');
        Config::set('services.gemini.key', '');

        $service = $this->app->make(LlmScamAnalysisService::class);
        $data = new ParsedListingInput(null, 500, null, 'Test Amsterdam');
        $market = ['average' => 1800, 'difference_percent' => -72];
        $rule = ['score' => 30, 'flags' => ['Prijs'], 'breakdown' => []];

        $out = $service->enhance($data, $market, $rule);

        $this->assertFalse($out['llm_used']);
        $this->assertSame(30, $out['score']);
        $this->assertSame(['Prijs'], $out['flags']);
        $this->assertNull($out['link_assessment']);
        $this->assertSame([], $out['observations']);
        $this->assertNotEmpty($out['recommendations']);
        $this->assertNotEmpty($out['what_to_verify']);
    }

    public function test_enhance_skips_http_when_use_llm_false_even_with_api_key(): void
    {
        Config::set('services.openai.key', 'sk-test');
        Config::set('services.openai.model', 'gpt-4o-mini');
        Config::set('services.openai.base_url', 'https://api.openai.com/v1');

        Http::fake();

        $service = $this->app->make(LlmScamAnalysisService::class);
        $data = new ParsedListingInput(null, 500, null, 'Test Amsterdam');
        $market = ['average' => 1800, 'difference_percent' => -72];
        $rule = ['score' => 30, 'flags' => ['Prijs'], 'breakdown' => []];

        $out = $service->enhance($data, $market, $rule, false);

        $this->assertFalse($out['llm_used']);
        Http::assertNothingSent();
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
                                'observations' => ['Obs 1', 'Obs 2'],
                                'risk_breakdown' => [['category' => 'Test', 'points' => 10, 'detail' => 'X']],
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = $this->app->make(LlmScamAnalysisService::class);
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
        $this->assertContains('Obs 1', $out['observations']);
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

        $service = $this->app->make(LlmScamAnalysisService::class);
        $data = new ParsedListingInput('https://example.com/ad', 800, null, str_repeat('y', 200));
        $market = ['average' => 1300, 'difference_percent' => -38];
        $rule = ['score' => 10, 'flags' => [], 'breakdown' => []];

        $out = $service->enhance($data, $market, $rule);

        $this->assertTrue($out['llm_used']);
        $this->assertStringContainsString('twijfelachtig', (string) $out['link_assessment']);
        $this->assertStringContainsString('Weinig woningdetails', (string) $out['link_assessment']);
    }

    public function test_uses_openrouter_when_openrouter_key_is_set(): void
    {
        Config::set('services.openai.key', 'sk-openai');
        Config::set('services.openrouter.key', 'sk-or-test');
        Config::set('services.openrouter.model', 'openai/gpt-4o-mini');
        Config::set('services.openrouter.base_url', 'https://openrouter.ai/api/v1');
        Config::set('services.openrouter.site_url', 'https://dehuurradar.nl');
        Config::set('services.openrouter.app_name', 'De Huurradar');

        Http::fake([
            'https://openrouter.ai/api/v1/chat/completions' => Http::response([
                'choices' => [
                    ['message' => ['content' => json_encode(['score' => 40, 'flags' => [], 'summary' => 'ok'])]],
                ],
            ]),
        ]);

        $service = $this->app->make(LlmScamAnalysisService::class);
        $data = new ParsedListingInput(null, 500, null, 'Test Amsterdam');
        $market = ['average' => 1800, 'difference_percent' => -72];
        $rule = ['score' => 30, 'flags' => ['Prijs'], 'breakdown' => []];

        $out = $service->enhance($data, $market, $rule);

        $this->assertTrue($out['llm_used']);
        Http::assertSent(fn ($request) => $request->url() === 'https://openrouter.ai/api/v1/chat/completions'
            && $request->hasHeader('Authorization', 'Bearer sk-or-test')
            && $request->hasHeader('HTTP-Referer', 'https://dehuurradar.nl')
            && $request->hasHeader('X-Title', 'De Huurradar')
            && $request['model'] === 'openai/gpt-4o-mini');
    }

    public function test_gemini_key_takes_precedence_and_uses_openai_compatible_endpoint(): void
    {
        Config::set('services.gemini.key', 'gemini-test');
        Config::set('services.gemini.model', 'gemini-flash-latest');
        Config::set('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta/openai');
        Config::set('services.openrouter.key', 'or-test');
        Config::set('services.openai.key', 'sk-test');

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'score' => 40,
                                // Ontleed teken (e + combinerende trema) moet als één 'ë' terugkomen.
                                'flags' => ["Gee\u{0308}xtraheerde prijs wijkt af"],
                                'summary' => 'Financiële druk bij de coöperatie.',
                                'recommendations' => [],
                                'what_to_verify' => [],
                                'risk_breakdown' => [],
                            ], JSON_UNESCAPED_UNICODE),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = $this->app->make(LlmScamAnalysisService::class);
        $data = new ParsedListingInput(null, 500, null, str_repeat('x', 200));
        $market = ['average' => 1800, 'difference_percent' => -72];
        $rule = ['score' => 30, 'flags' => ['Prijs'], 'breakdown' => []];

        $out = $service->enhance($data, $market, $rule);

        $this->assertTrue($out['llm_used']);
        $this->assertSame(40, $out['score']);
        $this->assertContains('Geëxtraheerde prijs wijkt af', $out['flags']);
        $this->assertStringContainsString('Financiële druk bij de coöperatie.', $out['summary']);
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer gemini-test')
            && $request['model'] === 'gemini-flash-latest');
    }
}
