<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Oppervlakte (m²) en indicatieve €/m²/maand naast de model-maandhuur.
 */
final class ListingSurfaceFacts extends Component
{
    public function __construct(
        public array $listingFacts,
        public string $variant = 'pdf',
    ) {}

    public function shouldRender(): bool
    {
        return isset($this->listingFacts['surface_m2']) && $this->listingFacts['surface_m2'] !== null;
    }

    public function render(): View
    {
        return view('components.listing-surface-facts');
    }
}
