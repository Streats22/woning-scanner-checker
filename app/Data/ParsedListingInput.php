<?php

namespace App\Data;

/**
 * Resultaat van het parsen van gebruikersinvoer (tekst of URL).
 */
final readonly class ParsedListingInput
{
    public function __construct(
        public ?string $sourceUrl,
        public ?int $price,
        public ?string $contact,
        public string $description,
        public ?float $surfaceM2 = null,
    )
    {
    }
}
