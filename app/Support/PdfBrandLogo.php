<?php

namespace App\Support;

/**
 * Logo voor Dompdf: alleen betrouwbare PNG data-URI's (geen SVG — kan 500 geven).
 * Publieke map eerst; daarna gebundelde kopie voor incomplete deploys.
 */
final class PdfBrandLogo
{
    public static function dataUri(): string
    {
        $paths = [
            public_path('img/huurradar-mark.png'),
            resource_path('assets/pdf/huurradar-mark.png'),
        ];

        foreach ($paths as $path) {
            if (is_readable($path)) {
                return 'data:image/png;base64,'.base64_encode((string) file_get_contents($path));
            }
        }

        return '';
    }
}
