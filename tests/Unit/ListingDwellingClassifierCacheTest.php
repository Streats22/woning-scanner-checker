<?php

namespace Tests\Unit;

use App\Data\ParsedListingInput;
use App\Services\ListingDwellingClassifier;
use Tests\TestCase;

class ListingDwellingClassifierCacheTest extends TestCase
{
    public function test_classify_same_input_returns_cached_result(): void
    {
        $c = new ListingDwellingClassifier;
        $d = new ParsedListingInput(null, 450, null, 'Studentenkamer te huur in Utrecht, €450 p.m.');

        $a = $c->classify($d);
        $b = $c->classify($d);

        $this->assertSame($a, $b);
    }
}
