<?php

namespace App\Services;

use App\Data\ParsedListingInput;

class ScamAnalysisService
{
    /**
     * @param  array{average: int, difference_percent: ?int}  $market
     * @return array{
     *     score: int,
     *     flags: array<int, string>,
     *     breakdown: array<int, array{category: string, points: int, detail: string}>
     * }
     */
    public function analyze(ParsedListingInput $data, array $market): array
    {
        $score = 0;
        $flags = [];
        $breakdown = [];

        if ($data->price && $data->price < ($market['average'] * 0.7)) {
            $score += 30;
            $flags[] = 'Prijs significant onder marktwaarde';
            $breakdown[] = [
                'category' => 'Prijs vs. benchmark',
                'points' => 30,
                'detail' => sprintf(
                    'Geëxtraheerde prijs €%s ligt ruim onder de gebruikte benchmark (€%s/maand). Dat kan legitiem zijn, maar past ook bij onderprijsing om vertrouwen te winnen.',
                    number_format($data->price, 0, ',', '.'),
                    number_format($market['average'], 0, ',', '.')
                ),
            ];
        }

        if (str_contains($data->description, 'WhatsApp')) {
            $score += 10;
            $flags[] = 'Alleen WhatsApp contact';
            $breakdown[] = [
                'category' => 'Contactkanaal',
                'points' => 10,
                'detail' => 'Er wordt WhatsApp genoemd als (enige) contactweg — veel scams vermijden traceerbare kanalen.',
            ];
        }

        if (preg_match('/alleen vandaag|snel reageren/i', $data->description)) {
            $score += 20;
            $flags[] = 'Urgente verkoopdruk (scam patroon)';
            $breakdown[] = [
                'category' => 'Druk & urgentie',
                'points' => 20,
                'detail' => 'Tekst creëert tijdsdruk (“vandaag”, “snel reageren”) — veel gebruikt om kritisch nadenken te verminderen.',
            ];
        }

        if (preg_match('/western union|vooruit betalen/i', $data->description)) {
            $score += 40;
            $flags[] = 'Bekend betaal-scam patroon';
            $breakdown[] = [
                'category' => 'Betalingswijze',
                'points' => 40,
                'detail' => 'Vermelding van Western Union, vooruitbetaling zonder bezichtiging of andere risicovolle betaalpatronen.',
            ];
        }

        return [
            'score' => min($score, 100),
            'flags' => $flags,
            'breakdown' => $breakdown,
        ];
    }
}
