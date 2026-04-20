<?php

namespace App\Services\Report;

use App\Data\ParsedListingInput;
use App\Services\ListingAddressParser;

/**
 * Vat geëxtraheerde advertentiegegevens samen voor API, rapport en PDF.
 */
final class ListingFactsAssembler
{
    public function __construct(
        private ListingAddressParser $addressParser,
    ) {}

    /**
     * @param  array{average: int, difference_percent: ?int}  $priceData
     * @return array<string, mixed>
     */
    public function build(ParsedListingInput $input, ?string $city, array $priceData): array
    {
        $addr = $this->addressParser->parseStreetAndNumber($input->description);
        $streetLine = null;
        if (! empty($addr['street'])) {
            $streetLine = $addr['street'];
            if (! empty($addr['number'])) {
                $streetLine .= ' '.$addr['number'];
            }
        }

        $nationalFallback = $city === null || $city === '';

        return [
            'city' => $city,
            'street' => $addr['street'],
            'house_number' => $addr['number'],
            'street_line' => $streetLine,
            'price_eur' => $input->price,
            'source_url' => $input->sourceUrl,
            'contact_hint' => $input->contact,
            'benchmark_monthly_eur' => $priceData['average'],
            'benchmark_diff_percent' => $priceData['difference_percent'],
            'benchmark_city' => $nationalFallback ? null : $city,
            'benchmark_scope' => $nationalFallback ? 'national' : 'municipality',
        ];
    }
}
