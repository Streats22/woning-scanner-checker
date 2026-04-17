<?php

namespace App\Services\Report;

use App\Models\Listing;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;

final class ReportPdfService
{
    public function render(Listing $listing): Response
    {
        $html = view('report-pdf', ['listing' => $listing])->render();

        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'woning-scam-rapport-'.($listing->report_slug ?? (string) $listing->id).'.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }
}
