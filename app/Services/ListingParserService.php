<?php

namespace App\Services;

use App\Data\ParsedListingInput;

class ListingParserService
{
    public function __construct(
        private ListingFetchService $fetch,
        private ListingSurfaceParser $surfaceParser,
    )
    {
    }

    public function parseInput(string $input): ParsedListingInput
    {
        $trimmed = trim($input);

        if ($this->isSingleLineUrl($trimmed)) {
            $url = $this->normalizeUrl($trimmed);
            if ($url === null) {
                return $this->fromPlainText($trimmed);
            }

            $out = $this->fetch->fetchPlainText($url);

            $text = $out['text'];

            return new ParsedListingInput(
                sourceUrl: $out['effective_url'],
                price: $this->price($text),
                contact: $this->contactHint($text),
                description: $text,
                surfaceM2: $this->surfaceParser->parse($text),
            );
        }

        return $this->fromPlainText($trimmed);
    }

    private function fromPlainText(string $text): ParsedListingInput
    {
        return new ParsedListingInput(
            sourceUrl: null,
            price: $this->price($text),
            contact: $this->contactHint($text),
            description: $text,
            surfaceM2: $this->surfaceParser->parse($text),
        );
    }

    private function isSingleLineUrl(string $s): bool
    {
        if ($s === '') {
            return false;
        }
        if (preg_match('/[\r\n]/', $s)) {
            return false;
        }

        return $this->normalizeUrl($s) !== null;
    }

    private function normalizeUrl(string $s): ?string
    {
        $s = trim($s);
        if ($s === '') {
            return null;
        }

        if (! preg_match('#^https?://#i', $s)) {
            if (! preg_match('#^(?:www\.)?[\w.-]+\.[a-z]{2,}#i', $s)) {
                return null;
            }
            $s = 'https://'.$s;
        }

        if (filter_var($s, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $parts = parse_url($s);
        if ($parts === false || empty($parts['scheme']) || ! in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return null;
        }

        return $s;
    }

    private function price(string $text): ?int
    {
        if (preg_match('/€\s?(\d{1,3}(?:\.\d{3})+)(?:[,.]\d{2})?\b/u', $text, $m)) {
            return (int) str_replace('.', '', $m[1]);
        }

        if (preg_match('/€\s?(\d{2,6})\b/u', $text, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    /**
     * Best-effort contact snippet for the report (not validation).
     * Previously only matched 06 + 8 consecutive digits; many listings use +31, spaced 06, email, or WhatsApp links.
     */
    private function contactHint(string $text): ?string
    {
        // International NL mobile: +31 6… / 0031 6… (with optional separators)
        if (preg_match('/(?:\+31|0031)[\s.-]?6[\s.-]?\d{8}/u', $text, $m)) {
            return $this->normalizeSpaces($m[0]);
        }
        if (preg_match('/\+316\d{8}/u', $text, $m)) {
            return $m[0];
        }
        if (preg_match('/00316\d{8}/u', $text, $m)) {
            return $m[0];
        }

        // Dutch mobile 06… (8 digits, optional grouping like 06 12 34 56 78)
        if (preg_match('/\b06[\s.-]*(?:\d[\s.-]*){7}\d\b/u', $text, $m)) {
            $digits = preg_replace('/\D+/', '', $m[0]);
            if (strlen($digits) === 10 && str_starts_with($digits, '06')) {
                return $this->normalizeSpaces($m[0]);
            }
        }

        // tel: links (HTML or plain)
        if (preg_match('/tel:([\d+\s().-]{8,40})/iu', $text, $m)) {
            $candidate = trim($m[1]);
            $digits = preg_replace('/\D+/', '', $candidate);
            if (strlen($digits) >= 9) {
                return $candidate;
            }
        }

        // mailto: or visible email
        if (preg_match('/mailto:([^\s"\'<>]+)/i', $text, $m)) {
            $addr = rawurldecode($m[1]);
            if (filter_var($addr, FILTER_VALIDATE_EMAIL)) {
                return $addr;
            }
        }
        if (preg_match('/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\b/u', $text, $m)) {
            return $m[0];
        }

        // WhatsApp deep links
        if (preg_match('#https?://wa\.me/([0-9]+)#i', $text, $m)) {
            return 'WhatsApp: '.$m[1];
        }
        if (preg_match('#https?://api\.whatsapp\.com/send\?phone=([0-9]+)#i', $text, $m)) {
            return 'WhatsApp: '.$m[1];
        }

        return null;
    }

    private function normalizeSpaces(string $s): string
    {
        return trim(preg_replace('/\s+/u', ' ', $s) ?? $s);
    }
}
