<?php

namespace App\Services\Report;

use App\Models\Listing;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;

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
            $html = view('report-pdf', [
                'listing' => $listing,
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
        $dompdf->render();

        $filename = 'huurradar-rapport-'.($listing->report_slug ?? (string) $listing->id).'.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }
}
