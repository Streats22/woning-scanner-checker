<?php

namespace Tests\Unit;

use App\Support\PdfBrandLogo;
use Tests\TestCase;

class PdfBrandLogoTest extends TestCase
{
    public function test_data_uri_is_non_empty_png_for_light_and_dark(): void
    {
        foreach (['light', 'dark'] as $theme) {
            $uri = PdfBrandLogo::dataUri($theme);

            $this->assertStringStartsWith('data:image/png;base64,', $uri, $theme);
            $this->assertGreaterThan(50, strlen($uri), $theme);
        }
    }
}
