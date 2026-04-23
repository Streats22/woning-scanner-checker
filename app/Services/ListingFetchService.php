<?php

namespace App\Services;

use App\Exceptions\ListingFetchException;
use App\Support\PublicHttpUrl;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Throwable;

class ListingFetchService
{
    /**
     * Download HTML and return readable plain text for analysis.
     * For public http:// URLs, tries https:// first (same path), then falls back to the original URL.
     *
     * @return array{text: string, effective_url: string}
     *
     * @throws ListingFetchException
     */
    public function fetchPlainText(string $url): array
    {
        if (!PublicHttpUrl::isSafeForServerFetch($url)) {
            throw new ListingFetchException(
                'Deze URL kan niet worden opgehaald: alleen openbare http(s)-adressen zijn toegestaan (geen interne, gereserveerde of onvolledige koppelingen).'
            );
        }

        foreach ($this->urlsToTry($url) as $candidate) {
            if (!PublicHttpUrl::isSafeForServerFetch($candidate)) {
                continue;
            }
            $text = $this->attemptFetch($candidate);
            if ($text !== null) {
                return ['text' => $text, 'effective_url' => $candidate];
            }
        }

        $this->throwDetailedFailure($url);
    }

    /**
     * @return list<string>
     */
    private function urlsToTry(string $url): array
    {
        $parts = parse_url($url);
        if ($parts === false || empty($parts['scheme'])) {
            return [$url];
        }

        if (strtolower($parts['scheme']) !== 'http') {
            return [$url];
        }

        $host = $parts['host'] ?? '';
        if ($host === '' || $this->isLocalOrDevHost($host)) {
            return [$url];
        }

        $httpsUrl = $this->toHttpsScheme($url);
        if ($httpsUrl === null || $httpsUrl === $url) {
            return [$url];
        }

        return [$httpsUrl, $url];
    }

    private function isLocalOrDevHost(string $host): bool
    {
        $h = strtolower($host);

        return $h === 'localhost'
            || $h === '127.0.0.1'
            || $h === '[::1]'
            || $h === '::1'
            || str_ends_with($h, '.test');
    }

    private function toHttpsScheme(string $httpUrl): ?string
    {
        if (! preg_match('#^http://#i', $httpUrl)) {
            return null;
        }

        return preg_replace('#^http://#i', 'https://', $httpUrl, 1);
    }

    private function httpClient(): PendingRequest
    {
        return Http::timeout(25)
            ->connectTimeout(12)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'nl-NL,nl;q=0.9,en;q=0.8',
            ])
            ->withOptions([
                'allow_redirects' => [
                    'max' => 5,
                    'on_redirect' => function ($request, $response, $nextUri): void {
                        $next = (string)$nextUri;
                        if (!PublicHttpUrl::isSafeForServerFetch($next)) {
                            throw new ListingFetchException(
                                'De verbinding is gestopt: de site stuurde door naar een niet-toegestaan adres. Plak de advertentie handmatig.'
                            );
                        }
                    },
                ],
            ]);
    }

    private function attemptFetch(string $url): ?string
    {
        $response = $this->getHttpResponse($url, softNetworkFailure: true);
        if ($response === null) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $html = $response->body();
        $text = $this->htmlToPlainText($html);

        if ($text === '' || mb_strlen($text) < 80) {
            return null;
        }

        return $text;
    }

    /**
     * @return \Illuminate\Http\Client\Response|null null when the request failed and the caller may try another URL (soft failure only)
     */
    private function getHttpResponse(string $url, bool $softNetworkFailure = true): ?\Illuminate\Http\Client\Response
    {
        try {
            return $this->httpClient()->get($url);
        } catch (ListingFetchException $e) {
            throw $e;
        } catch (Throwable $e) {
            if ($found = $this->unwrapListingFetchException($e)) {
                throw $found;
            }
            if ($softNetworkFailure) {
                return null;
            }
            throw new ListingFetchException(
                'Kan de pagina niet laden (netwerk, time-out of beveiliging). Controleer de URL of plak de advertentietekst handmatig.'
            );
        }
    }

    private function unwrapListingFetchException(Throwable $e): ?ListingFetchException
    {
        $c = $e;
        for ($i = 0; $c !== null && $i < 12; $i++) {
            if ($c instanceof ListingFetchException) {
                return $c;
            }
            $c = $c->getPrevious();
        }

        return null;
    }

    private function throwDetailedFailure(string $url): never
    {
        $response = $this->getHttpResponse($url, softNetworkFailure: false);
        if (! $response->successful()) {
            $status = $response->status();

            if ($status === 403) {
                throw new ListingFetchException(
                    'Deze website staat niet toe dat wij de pagina automatisch ophalen (toegang geweigerd). Kopieer de advertentietekst en plak die hier handmatig.'
                );
            }

            throw new ListingFetchException('Kan de pagina niet laden (HTTP '.$status.').');
        }

        $html = $response->body();
        $text = $this->htmlToPlainText($html);

        if ($text === '' || mb_strlen($text) < 80) {
            throw new ListingFetchException('Er is te weinig tekst uit deze pagina gehaald. Plak de advertentie handmatig.');
        }

        throw new ListingFetchException('Kan de pagina niet laden. Controleer de URL of plak de advertentietekst handmatig.');
    }

    private function htmlToPlainText(string $html): string
    {
        $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html) ?? $html;
        $html = preg_replace('#<style\b[^>]*>.*?</style>#is', '', $html) ?? $html;
        $html = preg_replace('#<noscript\b[^>]*>.*?</noscript>#is', '', $html) ?? $html;

        $previous = libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
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
