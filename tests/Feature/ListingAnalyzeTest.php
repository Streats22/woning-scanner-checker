<?php

namespace Tests\Feature;

use App\Models\Listing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ListingAnalyzeTest extends TestCase
{
    use RefreshDatabase;

    public function test_analyze_returns_contract_and_persists_listing(): void
    {
        $text = 'Te huur Amsterdam € 500 per maand. Neem alleen WhatsApp contact op. Western Union betaling.';

        $response = $this->postJson('/api/analyze', ['text' => $text]);

        $response->assertOk()
            ->assertJsonStructure([
                'score',
                'flags',
                'market' => ['average', 'difference_percent'],
                'summary',
                'summary_short',
                'narrative',
                'observations',
                'llm_used',
                'link_assessment',
                'recommendations',
                'what_to_verify',
                'risk_breakdown',
                'rule_score',
                'methodology',
                'market_context',
                'listing_fit' => ['tier', 'score', 'reason_codes'],
                'listing_facts' => [
                    'city',
                    'street',
                    'house_number',
                    'street_line',
                    'price_eur',
                    'source_url',
                    'contact_hint',
                    'benchmark_monthly_eur',
                    'benchmark_diff_percent',
                    'benchmark_city',
                    'benchmark_scope',
                    'surface_m2',
                    'price_per_m2_month_eur',
                    'benchmark_per_m2_month_eur',
                    'adjusted_benchmark_per_m2_month_eur',
                    'per_m2_vs_adjusted_percent',
                    'small_surface',
                    'dwelling' => [
                        'kind',
                        'kind_confidence',
                        'rental_sector',
                        'sector_confidence',
                        'signals',
                    ],
                ],
                'id',
                'report_url',
                'report_pdf_url',
                'report_slug',
            ])
            ->assertJson([
                'llm_used' => false,
                'link_assessment' => null,
                'observations' => [],
            ]);

        $this->assertDatabaseCount('listings', 1);
        $listing = Listing::first();
        $this->assertSame($text, $listing->raw_input);
        $this->assertIsArray($listing->report_snapshot);
        $this->assertArrayHasKey('recommendations', $listing->report_snapshot);
        $this->assertArrayHasKey('listing_fit', $listing->report_snapshot);
        $this->assertSame([], $listing->report_snapshot['observations'] ?? null);
        $this->assertNull($listing->source_url);
        $this->assertNotNull($listing->ai_summary);
        $this->assertMatchesRegularExpression(
            '/^listing-[a-z0-9]+-[a-z0-9]+-[a-z0-9]+-\d{4}-\d{2}-\d{2}-\d{2}-\d{2}-\d{2}-\d+$/',
            $listing->report_slug ?? ''
        );
        $this->assertStringContainsString($listing->report_slug, $response->json('report_url'));
        $this->assertStringContainsString($listing->report_slug.'/pdf', $response->json('report_pdf_url'));
    }

    public function test_analyze_does_not_call_openai_when_use_ai_is_false(): void
    {
        Config::set('services.openai.key', 'sk-test');
        Config::set('services.openai.model', 'gpt-4o-mini');
        Config::set('services.openai.base_url', 'https://api.openai.com/v1');

        Http::fake();

        $text = 'Te huur Amsterdam € 500 per maand. Neem alleen WhatsApp contact op.';

        $response = $this->postJson('/api/analyze', [
            'text' => $text,
            'use_ai' => false,
        ]);

        $response->assertOk()->assertJson(['llm_used' => false]);

        Http::assertNothingSent();
    }

    public function test_analyze_fetches_plain_text_when_input_is_single_url(): void
    {
        Http::fake(function () {
            return Http::response(
                '<html lang="nl"><head><title>X</title></head><body><p>Te huur Amsterdam € 900 per maand.</p><p>Neem contact op via WhatsApp.</p>'
                .str_repeat('<p>Extra context om de minimale lengte te halen.</p>', 12)
                .'</body></html>',
                200
            );
        });

        $url = 'https://www.123wonen.nl/huur/alkmaar/hoekwoning/1e+tuindwarsstraat-1221-20';

        $response = $this->postJson('/api/analyze', ['text' => $url]);

        $response->assertOk();
        Http::assertSent(fn ($request) => str_contains($request->url(), '123wonen.nl'));

        $listing = Listing::first();
        $this->assertSame($url, $listing->source_url);
        $this->assertStringContainsString('WhatsApp', $listing->description);
    }

    public function test_analyze_tries_https_first_when_input_url_uses_http_scheme(): void
    {
        $body = '<html><body><p>Te huur Amsterdam € 900 per maand.</p>'
            .str_repeat('<p>Extra context om de minimale lengte te halen.</p>', 14)
            .'</body></html>';

        Http::fake([
            'https://public.example/*' => Http::response($body, 200),
            'http://public.example/*' => Http::response('<html><body>wrong</body></html>', 200),
        ]);

        $input = 'http://public.example/kamer/amsterdam';
        $response = $this->postJson('/api/analyze', ['text' => $input]);

        $response->assertOk();
        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://public.example'));
        Http::assertNotSent(fn ($request) => str_starts_with($request->url(), 'http://public.example'));

        $listing = Listing::first();
        $this->assertSame('https://public.example/kamer/amsterdam', $listing->source_url);
    }

    public function test_analyze_falls_back_to_http_when_https_fails(): void
    {
        $body = '<html><body><p>Te huur Utrecht € 850 per maand.</p>'
            .str_repeat('<p>Extra context om de minimale lengte te halen.</p>', 14)
            .'</body></html>';

        Http::fake([
            'https://fallback.example/*' => Http::response('', 502),
            'http://fallback.example/*' => Http::response($body, 200),
        ]);

        $input = 'http://fallback.example/ad';
        $response = $this->postJson('/api/analyze', ['text' => $input]);

        $response->assertOk();

        $listing = Listing::first();
        $this->assertSame('http://fallback.example/ad', $listing->source_url);
        $this->assertStringContainsString('Utrecht', $listing->description);
    }

    public function test_analyze_returns_validation_error_when_fetch_fails(): void
    {
        Http::fake([
            'https://broken.example/*' => Http::response('', 500),
        ]);

        $response = $this->postJson('/api/analyze', [
            'text' => 'https://broken.example/page',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['text']);
    }

    public function test_analyze_returns_validation_error_when_http_connection_fails(): void
    {
        Http::fake(function () {
            throw new ConnectionException('Connection refused');
        });

        $response = $this->postJson('/api/analyze', [
            'text' => 'https://example.com/advertentie',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['text']);
    }

    public function test_report_page_is_public_and_indexable(): void
    {
        $listing = Listing::create([
            'raw_input' => 'Test',
            'price' => 1000,
            'currency' => 'EUR',
            'city' => 'Utrecht',
            'description' => 'Test',
            'contact' => null,
            'scam_score' => 40,
            'scam_flags' => ['Test flag'],
            'ai_summary' => 'Samenvatting',
            'market_average' => 1600,
            'market_difference_percent' => -38,
        ]);

        $listing->update([
            'report_slug' => Listing::buildReportSlug(
                $listing->created_at,
                $listing->id,
                $listing->city,
                $listing->description,
            ),
        ]);

        $this->get('/report/'.$listing->id)->assertRedirect('/report/'.$listing->report_slug);
        $this->get('/report/'.$listing->report_slug)->assertOk()
            ->assertSee('Gedeeld rapport', false)
            ->assertSee('index, follow', false);

        $this->get('/report/'.$listing->report_slug.'/pdf')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->get('/report/'.$listing->report_slug.'/pdf?theme=dark&locale=en')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $redirectPdf = $this->get('/report/'.$listing->id.'/pdf?theme=dark&locale=en');
        $redirectPdf->assertRedirect();
        $loc = (string) $redirectPdf->headers->get('Location');
        $this->assertStringContainsString('/report/'.$listing->report_slug.'/pdf', $loc);
        parse_str((string) parse_url($loc, PHP_URL_QUERY), $pdfQs);
        $this->assertSame('dark', $pdfQs['theme'] ?? null);
        $this->assertSame('en', $pdfQs['locale'] ?? null);
    }

    public function test_report_pdf_survives_malformed_report_snapshot_shapes(): void
    {
        $listing = Listing::create([
            'raw_input' => 'Test',
            'price' => 1000,
            'currency' => 'EUR',
            'city' => 'Utrecht',
            'description' => 'Test',
            'contact' => null,
            'scam_score' => 40,
            'scam_flags' => ['Test flag'],
            'ai_summary' => 'Samenvatting',
            'market_average' => 1600,
            'market_difference_percent' => -38,
            'report_snapshot' => [],
        ]);

        $listing->getConnection()->table('listings')->where('id', $listing->id)->update([
            'report_snapshot' => json_encode([
                'recommendations' => 'not-an-array',
                'what_to_verify' => 123,
                'risk_breakdown' => [
                    ['category' => 'X', 'points' => 1, 'detail' => 'ok'],
                    'not-a-row',
                ],
            ]),
        ]);

        $this->get('/report/'.$listing->id.'/pdf?theme=dark&locale=en')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_report_redirects_when_slug_prefix_differs_but_trailing_id_matches(): void
    {
        $listing = Listing::create([
            'raw_input' => 'Test',
            'price' => 1000,
            'currency' => 'EUR',
            'city' => 'Utrecht',
            'description' => 'Test',
            'contact' => null,
            'scam_score' => 40,
            'scam_flags' => ['Test flag'],
            'ai_summary' => 'Samenvatting',
            'market_average' => 1600,
            'market_difference_percent' => -38,
        ]);

        $listing->update([
            'report_slug' => Listing::buildReportSlug(
                $listing->created_at,
                $listing->id,
                $listing->city,
                $listing->description,
            ),
        ]);
        $listing->refresh();

        $legacySlug = 'advertentie-2020-01-01-00-00-00-'.$listing->id;

        $this->get('/report/'.$legacySlug)->assertRedirect('/report/'.$listing->report_slug);
    }
}
