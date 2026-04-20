<?php

namespace App\Services\Report;

use App\Data\ParsedListingInput;
use App\Services\ListingAddressParser;
use App\Services\ListingDwellingClassifier;
use App\Support\RentBenchmarkMap;
use App\Support\RentPerSquareMeterModel;

/**
 * Vat geëxtraheerde advertentiegegevens samen voor API, rapport en PDF.
 */
final class ListingFactsAssembler
{
    public function __construct(
        private ListingAddressParser $addressParser,
        private ListingDwellingClassifier $dwellingClassifier,
    ) {}

    /**
     * @param  array{average: int, difference_percent: ?int}  $priceData
     * @return array<string, mixed>
     */
    public function build(ParsedListingInput $input, ?string $city, array $priceData): array
    {
        $addr = $this->addressParser->parseStreetAndNumber($input->description, $input->sourceUrl);
        $streetLine = null;
        if (! empty($addr['street'])) {
            $streetLine = $addr['street'];
            if (! empty($addr['number'])) {
                $streetLine .= ' '.$addr['number'];
            }
        }

        $nationalFallback = $city === null || $city === '';

        $canonical = RentBenchmarkMap::resolveCanonicalCity($city);
        $displayCity = (! $nationalFallback && $canonical !== null && $canonical !== '')
            ? RentBenchmarkMap::displayPlaceLabel($canonical, $input->description, $input->sourceUrl)
            : null;

        $perM2 = RentPerSquareMeterModel::build(
            $input->price,
            $input->surfaceM2,
            $priceData['average'],
        );

        return [
            'city' => $displayCity,
            'street' => $addr['street'],
            'house_number' => $addr['number'],
            'street_line' => $streetLine,
            'price_eur' => $input->price,
            'source_url' => $input->sourceUrl,
            'contact_hint' => $input->contact,
            'benchmark_monthly_eur' => $priceData['average'],
            'benchmark_diff_percent' => $priceData['difference_percent'],
            'benchmark_city' => $nationalFallback ? null : $displayCity,
            'benchmark_scope' => $nationalFallback ? 'national' : 'municipality',
            'dwelling' => $this->dwellingClassifier->classify($input),
            'surface_m2' => $perM2['surface_m2'],
            'price_per_m2_month_eur' => $perM2['price_per_m2_month_eur'],
            'benchmark_per_m2_month_eur' => $perM2['benchmark_per_m2_month_eur'],
            'adjusted_benchmark_per_m2_month_eur' => $perM2['adjusted_benchmark_per_m2_month_eur'],
            'per_m2_vs_adjusted_percent' => $perM2['per_m2_vs_adjusted_percent'],
            'small_surface' => $perM2['small_surface'],
        ];
    }
}
