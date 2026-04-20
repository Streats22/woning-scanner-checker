<?php

namespace App\View\Components;

use App\Support\ListingDwellingPresentation;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Gedeelde weergave van woning-classificatie (PDF + web rapport).
 */
final class ListingDwellingFacts extends Component
{
    /** @var array{kind_line: string, sector_line: string, signals: ?string} */
    public array $lines;

    /**
     * @param  array<string, mixed>  $dwelling
     */
    public function __construct(
        public array $dwelling,
        public string $variant = 'pdf',
    ) {
        $this->lines = ListingDwellingPresentation::lines($dwelling);
    }

    public function render(): View
    {
        return view('components.listing-dwelling-facts');
    }
}
