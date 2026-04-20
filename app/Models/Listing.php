<?php

namespace App\Models;

use App\Services\ListingAddressParser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Listing extends Model
{
    protected $fillable = [
        'raw_input',
        'source_url',
        'price',
        'currency',
        'city',
        'postal_code',
        'description',
        'contact',
        'scam_score',
        'scam_flags',
        'ai_summary',
        'market_average',
        'market_difference_percent',
        'report_slug',
        'report_snapshot',
    ];

    /**
     * Leesbare URL: listing-{stad}-{straat}-{nr}-{datum}-{tijd}-{id}.
     * (Eerdere vorm advertentie-{datum}-{tijd}-{id} blijft via DB/oude links herkenbaar.)
     */
    public static function buildReportSlug(
        Carbon $createdAt,
        int $id,
        ?string $city,
        ?string $description,
        ?string $sourceUrl = null,
        ?ListingAddressParser $addressParser = null,
    ): string {
        $parser = $addressParser ?? new ListingAddressParser;
        $addr = $parser->parseStreetAndNumber($description, $sourceUrl);

        $citySlug = self::slugSegment($city);
        $streetSlug = self::slugSegment($addr['street']);
        $numberSlug = self::slugifyHouseNumber($addr['number']);

        $parts = [
            'listing',
            $citySlug,
            $streetSlug,
            $numberSlug,
            $createdAt->format('Y-m-d'),
            $createdAt->format('H-i-s'),
            (string) $id,
        ];

        $slug = implode('-', $parts);

        if (strlen($slug) > 191) {
            $slug = self::shortenSlugParts($parts, $createdAt, $id);
        }

        return $slug;
    }

    /**
     * @param  list<string>  $parts
     */
    private static function shortenSlugParts(array $parts, Carbon $createdAt, int $id): string
    {
        // [0]=listing [1]=city [2]=street [3]=nr [4]=date [5]=time [6]=id
        while (strlen(implode('-', $parts)) > 191 && strlen($parts[2]) > 4) {
            $parts[2] = substr($parts[2], 0, -1);
        }
        while (strlen(implode('-', $parts)) > 191 && strlen($parts[1]) > 4) {
            $parts[1] = substr($parts[1], 0, -1);
        }

        $slug = implode('-', $parts);
        if (strlen($slug) > 191) {
            return sprintf(
                'listing-%s-%s-%d',
                $createdAt->format('Y-m-d'),
                $createdAt->format('H-i-s'),
                $id
            );
        }

        return $slug;
    }

    private static function slugifyHouseNumber(?string $n): string
    {
        if ($n === null || trim($n) === '') {
            return 'x';
        }
        $n = mb_strtolower(trim($n));
        $n = preg_replace('/[^a-z0-9]+/', '', $n) ?? '';

        return $n !== '' ? substr($n, 0, 12) : 'x';
    }

    private static function slugSegment(?string $s): string
    {
        if ($s === null || trim($s) === '') {
            return 'onbekend';
        }

        $s = mb_strtolower(trim($s));
        $trans = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        if ($trans !== false) {
            $s = $trans;
        }

        // Geen extra '-' binnen een segment (anders is het aantal URL-segmenten ambigu).
        $s = preg_replace('/[^a-z0-9]+/', '', $s) ?? '';

        if ($s === '') {
            return 'onbekend';
        }

        return substr($s, 0, 48);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scam_flags' => 'array',
            'report_snapshot' => 'array',
        ];
    }
}
