<?php

namespace Tests\Unit;

use App\Support\PdfBrandLogo;
use Tests\TestCase;

class PdfBrandLogoTest extends TestCase
{
    public function test_data_uri_is_non_empty_png(): void
    {
        $uri = PdfBrandLogo::dataUri();

        $this->assertStringStartsWith('data:image/png;base64,', $uri);
        $this->assertGreaterThan(50, strlen($uri));
    }
}
