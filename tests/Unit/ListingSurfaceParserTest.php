<?php

namespace Tests\Unit;

use App\Services\ListingSurfaceParser;
use PHPUnit\Framework\TestCase;

class ListingSurfaceParserTest extends TestCase
{
    public function test_parses_keyword_woonoppervlakte(): void
    {
        $p = new ListingSurfaceParser;
        $text = 'Woonoppervlakte 45 m², centrum Amsterdam.';
        $this->assertSame(45.0, $p->parse($text));
    }

    public function test_prefers_keyword_match_over_larger_generic_number(): void
    {
        $p = new ListingSurfaceParser;
        $text = 'Perceel 500 m². Woonoppervlakte: 62 m2.';
        $this->assertSame(62.0, $p->parse($text));
    }

    public function test_falls_back_to_first_plausible_m2(): void
    {
        $p = new ListingSurfaceParser;
        $text = 'Te huur studio 28 m² in Utrecht.';
        $this->assertSame(28.0, $p->parse($text));
    }

    public function test_returns_null_for_out_of_range(): void
    {
        $p = new ListingSurfaceParser;
        $this->assertNull($p->parse('Kamer 4 m²'));
        $this->assertNull($p->parse(''));
    }
}
