<?php

namespace App\Services;

use App\Data\ParsedListingInput;

class ListingParserService
{
    public function __construct(
        private ListingFetchService $fetch,
    ) {}

    public function parseInput(string $input): ParsedListingInput
    {
        $trimmed = trim($input);

        if ($this->isSingleLineUrl($trimmed)) {
            $url = $this->normalizeUrl($trimmed);
            if ($url === null) {
                return $this->fromPlainText($trimmed);
            }

            $text = $this->fetch->fetchPlainText($url);

            return new ParsedListingInput(
                sourceUrl: $url,
                price: $this->price($text),
                contact: $this->phone($text),
                description: $text,
            );
        }

        return $this->fromPlainText($trimmed);
    }

    private function fromPlainText(string $text): ParsedListingInput
    {
        return new ParsedListingInput(
            sourceUrl: null,
            price: $this->price($text),
            contact: $this->phone($text),
            description: $text,
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

    private function phone(string $text): ?string
    {
        if (preg_match('/06[\s-]?\d{8}/', $text, $m)) {
            return $m[0];
        }

        return null;
    }
}
