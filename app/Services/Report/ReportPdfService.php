<?php

namespace App\Services\Report;

use App\Models\Listing;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ReportPdfService
{
    /**
     * @param  'light'|'dark'  $theme
     * @param  'nl'|'en'  $locale
     */
    public function render(Listing $listing, string $theme = 'light', string $locale = 'nl'): Response
    {
        $theme = in_array($theme, ['light', 'dark'], true) ? $theme : 'light';
        $locale = in_array($locale, ['nl', 'en'], true) ? $locale : 'nl';

        $previousLocale = app()->getLocale();
        app()->setLocale($locale);
        try {
            $listingForPdf = $this->listingWithSanitizedPdfFields($listing);
            $html = view('report-pdf', [
                'listing' => $listingForPdf,
                'theme' => $theme,
                'locale' => $locale,
            ])->render();
        } finally {
            app()->setLocale($previousLocale);
        }

        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        // data:-URI (logo in PDF) en lokale assets onder public/
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', realpath(public_path()) ?: public_path());

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        try {
            $dompdf->render();
        } catch (Throwable $e) {
            Log::error('report_pdf_render_failed', [
                'listing_id' => $listing->id,
                'exception' => $e,
            ]);

            throw $e;
        }

        $filename = 'huurradar-rapport-'.($listing->report_slug ?? (string) $listing->id).'.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    private function listingWithSanitizedPdfFields(Listing $listing): Listing
    {
        $clone = clone $listing;
        $clone->setAttribute('report_snapshot', $this->sanitizeReportSnapshot($listing->report_snapshot));
        $clone->setAttribute('scam_flags', $this->sanitizeStringList($listing->scam_flags));

        return $clone;
    }

    /**
     * @return array<string, mixed>
     */
    private function sanitizeReportSnapshot(mixed $snapshot): array
    {
        if (! is_array($snapshot)) {
            return [];
        }

        foreach (['recommendations', 'what_to_verify'] as $key) {
            if (! isset($snapshot[$key]) || ! is_array($snapshot[$key])) {
                $snapshot[$key] = [];
            }
        }

        $breakdown = $snapshot['risk_breakdown'] ?? [];
        $snapshot['risk_breakdown'] = array_values(array_filter(
            is_array($breakdown) ? $breakdown : [],
            static fn ($row) => is_array($row),
        ));

        return $snapshot;
    }

    /**
     * @return array<int, string>
     */
    private function sanitizeStringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $out = [];
        foreach ($value as $item) {
            if (is_string($item) && $item !== '') {
                $out[] = $item;
            }
        }

        return $out;
    }
}
