<?php

namespace App\Http\Controllers;

use App\Exceptions\ListingFetchException;
use App\Models\Listing;
use App\Services\ListingContentFitService;
use App\Services\ListingParserService;
use App\Services\LlmScamAnalysisService;
use App\Services\LocationService;
use App\Services\PriceAnalysisService;
use App\Services\Report\ListingAnalyzeResultAssembler;
use App\Services\Report\ReportListingResolver;
use App\Services\Report\ReportPdfService;
use App\Services\Report\ReportUrlGenerator;
use App\Services\ScamAnalysisService;
use App\Support\RentBenchmarkMap;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ListingController extends Controller
{
    public function __construct(
        private ListingParserService $parser,
        private ScamAnalysisService $scam,
        private PriceAnalysisService $price,
        private LocationService $location,
        private LlmScamAnalysisService $llmScam,
        private ReportListingResolver $listingResolver,
        private ReportPdfService $pdfRenderer,
        private ReportUrlGenerator $reportUrlGenerator,
        private ListingAnalyzeResultAssembler $analyzeResultAssembler,
        private ListingContentFitService $listingContentFit,
    ) {}

    public function analyze(Request $request)
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:65535'],
            'use_ai' => ['sometimes', 'boolean'],
        ]);

        $useAi = $request->boolean('use_ai');

        try {
            $data = $this->parser->parseInput($validated['text']);
        } catch (ListingFetchException $e) {
            throw ValidationException::withMessages([
                'text' => $e->getMessage(),
            ]);
        }

        $city = $this->location->detectCity($data->description, $data->sourceUrl);

        $priceData = $this->price->analyze(
            $city,
            $data->price
        );

        $ruleScam = $this->scam->analyze($data, $priceData);
        $analysis = $this->llmScam->enhance($data, $priceData, $ruleScam, $useAi);

        $listingFit = $this->listingContentFit->assess($data);

        $reportSnapshot = $this->analyzeResultAssembler->buildReportSnapshot($analysis, $priceData, $listingFit, $data, $city);

        $listing = Listing::create([
            'raw_input' => $validated['text'],
            'source_url' => $data->sourceUrl,
            'price' => $data->price,
            'currency' => 'EUR',
            'city' => $city,
            'postal_code' => null,
            'description' => $data->description,
            'contact' => $data->contact,
            'scam_score' => $analysis['score'],
            'scam_flags' => $analysis['flags'],
            'ai_summary' => $analysis['summary'],
            'market_average' => $priceData['average'],
            'market_difference_percent' => $priceData['difference_percent'],
            'report_snapshot' => $reportSnapshot,
        ]);

        $displayForSlug = ($listing->city !== null && $listing->city !== '')
            ? (RentBenchmarkMap::displayPlaceLabel($listing->city, $listing->description, $listing->source_url) ?? $listing->city)
            : null;

        $listing->update([
            'report_slug' => Listing::buildReportSlug(
                $listing->created_at,
                $listing->id,
                $displayForSlug ?? $listing->city,
                $listing->description,
                $listing->source_url,
            ),
        ]);
        $listing->refresh();

        $urls = $this->reportUrlGenerator->absoluteUrls($request, $listing);
        $payload = $this->analyzeResultAssembler->buildApiPayload($listing, $analysis, $priceData, $urls, $listingFit);

        return response()->json($payload);
    }

    public function showReport(string $idOrSlug): View|RedirectResponse
    {
        $listing = $this->listingResolver->resolve($idOrSlug);

        if ($redirect = $this->canonicalReportRedirect($listing, $idOrSlug, 'report.show')) {
            return $redirect;
        }

        return view('report', [
            'listing' => $listing,
        ]);
    }

    public function reportPdf(Request $request, string $idOrSlug): Response|RedirectResponse
    {
        $listing = $this->listingResolver->resolve($idOrSlug);

        if ($redirect = $this->canonicalReportRedirect($listing, $idOrSlug, 'report.pdf')) {
            return $redirect;
        }

        $theme = $request->query('theme', 'light');
        $locale = $request->query('locale', 'nl');
        $theme = is_string($theme) ? $theme : 'light';
        $locale = is_string($locale) ? $locale : 'nl';

        return $this->pdfRenderer->render($listing, $theme, $locale);
    }

    private function canonicalReportRedirect(Listing $listing, string $idOrSlug, string $routeName): ?RedirectResponse
    {
        if ($listing->report_slug !== null && $idOrSlug !== $listing->report_slug) {
            $url = route($routeName, ['idOrSlug' => $listing->report_slug]);
            $qs = request()->getQueryString();
            if ($qs !== null && $qs !== '') {
                $url .= '?'.$qs;
            }

            return redirect($url, 301);
        }

        return null;
    }
}
