<?php

namespace App\Support;

/**
 * Logo voor Dompdf: alleen betrouwbare PNG data-URI's (geen SVG — Dompdf).
 * Bron: zelfde mark als de site (frontend BrandMark + favicon) — vector staat in
 * public/img/huurradar-mark.svg; PNG daaruit rasteriseren bij wijzigingen (bijv. sharp).
 * Publieke map eerst; daarna gebundelde kopie voor incomplete deploys.
 */
final class PdfBrandLogo
{
    /**
     * @param  'light'|'dark'  $theme  Zelfde tokens als frontend :root / html.dark (main.css).
     */
    public static function dataUri(string $theme = 'light'): string
    {
        $theme = $theme === 'dark' ? 'dark' : 'light';
        $file = $theme === 'dark' ? 'huurradar-mark-dark.png' : 'huurradar-mark.png';

        $paths = [
            public_path('img/'.$file),
            resource_path('assets/pdf/'.$file),
        ];

        foreach ($paths as $path) {
            if (is_readable($path)) {
                return 'data:image/png;base64,'.base64_encode((string) file_get_contents($path));
            }
        }

        return '';
    }
}
