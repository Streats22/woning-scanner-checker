<?php

namespace App\Services\Report;

use App\Models\Listing;
use Illuminate\Http\Request;

/**
 * Absolute rapport-URLs op basis van het huidige request (zelfde host/poort als de API).
 */
final class ReportUrlGenerator
{
    /**
     * @return array{report_url: string, report_pdf_url: string}
     */
    public function absoluteUrls(Request $request, Listing $listing): array
    {
        $slug = $listing->report_slug;
        $base = $request->getSchemeAndHttpHost();
        $path = '/report/'.$slug;

        return [
            'report_url' => $base.$path,
            'report_pdf_url' => $base.$path.'/pdf',
        ];
    }
}
