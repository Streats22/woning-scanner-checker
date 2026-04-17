<?php

namespace App\Services;

use App\Exceptions\ListingFetchException;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Support\Facades\Http;

class ListingFetchService
{
    /**
     * Download HTML and return readable plain text for analysis.
     *
     * @throws ListingFetchException
     */
    public function fetchPlainText(string $url): string
    {
        try {
            $response = Http::timeout(25)
                ->connectTimeout(12)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'nl-NL,nl;q=0.9,en;q=0.8',
                ])
                ->get($url);
        } catch (HttpClientException) {
            throw new ListingFetchException(
                'Kan de pagina niet laden (netwerk, time-out of beveiliging). Controleer de URL of plak de advertentietekst handmatig.'
            );
        }

        if (! $response->successful()) {
            throw new ListingFetchException('Kan de pagina niet laden (HTTP '.$response->status().').');
        }

        $html = $response->body();
        $text = $this->htmlToPlainText($html);

        if ($text === '' || mb_strlen($text) < 80) {
            throw new ListingFetchException('Er is te weinig tekst uit deze pagina gehaald. Plak de advertentie handmatig.');
        }

        return $text;
    }

    private function htmlToPlainText(string $html): string
    {
        $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html) ?? $html;
        $html = preg_replace('#<style\b[^>]*>.*?</style>#is', '', $html) ?? $html;
        $html = preg_replace('#<noscript\b[^>]*>.*?</noscript>#is', '', $html) ?? $html;

        $previous = libxml_use_internal_errors(true);
        $dom = new \DOMDocument;
        // Avoid deprecated mb_convert_encoding(..., 'HTML-ENTITIES', 'UTF-8') (PHP 8.2+)
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $body = $dom->getElementsByTagName('body')->item(0);
        $text = $body ? $body->textContent : strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        $text = trim($text);

        if ($text === '') {
            $text = trim(preg_replace('/\s+/u', ' ', strip_tags($html)) ?? '');
        }

        return $text;
    }
}
