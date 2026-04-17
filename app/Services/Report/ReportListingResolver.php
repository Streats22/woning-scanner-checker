<?php

namespace App\Services\Report;

use App\Models\Listing;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Zoekt een listing op numeriek id, exacte report_slug, of suffix-patroon met listing-id.
 */
final class ReportListingResolver
{
    public function resolve(string $idOrSlug): Listing
    {
        if (ctype_digit($idOrSlug)) {
            return Listing::findOrFail((int) $idOrSlug);
        }

        $bySlug = Listing::where('report_slug', $idOrSlug)->first();
        if ($bySlug !== null) {
            return $bySlug;
        }

        if (preg_match('/-(\d{4}-\d{2}-\d{2}-\d{2}-\d{2}-\d{2})-(\d+)$/', $idOrSlug, $m)) {
            $listing = Listing::find((int) $m[2]);
            if ($listing !== null) {
                return $listing;
            }
        }

        throw (new ModelNotFoundException)->setModel(Listing::class);
    }
}
