<?php

namespace Tests\Unit;

use App\Support\ListingDwellingPresentation;
use Tests\TestCase;

class ListingDwellingPresentationTest extends TestCase
{
    public function test_lines_joins_labels_and_confidence(): void
    {
        $lines = ListingDwellingPresentation::lines([
            'kind' => 'room',
            'kind_confidence' => 'high',
            'rental_sector' => 'private',
            'sector_confidence' => 'medium',
            'signals' => ['kamer'],
        ]);

        $this->assertStringContainsString('·', $lines['kind_line']);
        $this->assertStringContainsString('·', $lines['sector_line']);
        $this->assertSame('kamer', $lines['signals']);
    }
}
