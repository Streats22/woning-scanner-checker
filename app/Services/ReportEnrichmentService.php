<?php

namespace App\Services;

use App\Data\ParsedListingInput;

class ReportEnrichmentService
{
    /**
     * Vult aanbevelingen en controles op basis van regels + score (zonder LLM-extras).
     *
     * @param  array{average: int, difference_percent: ?int}  $market
     * @param  array{score: int, flags: array<int, string>, breakdown: array<int, array{category: string, points: int, detail: string}>}  $ruleScam
     * @return array{recommendations: array<int, string>, what_to_verify: array<int, string>, market_context: string, methodology: string}
     */
    public function buildRuleBasedExtras(ParsedListingInput $data, array $market, array $ruleScam): array
    {
        $recommendations = [];
        $checks = [];

        if ($ruleScam['score'] >= 61) {
            $recommendations[] = 'Stuur geen geld of persoonsgegevens voordat je de woning en verhuurder hebt geverifieerd.';
            $recommendations[] = 'Vraag om een videocall of fysieke bezichtiging via een betrouwbaar kanaal (niet alleen WhatsApp).';
        } elseif ($ruleScam['score'] >= 31) {
            $recommendations[] = 'Wees extra kritisch: controleer adres, KvK/identiteit van de verhuurder en zoek de advertentie elders terug.';
        } else {
            $recommendations[] = 'Blijf voorzichtig: controleer altijd sleuteloverdracht en contract voordat je betaalt.';
        }

        if ($data->sourceUrl) {
            $checks[] = 'Vergelijk de advertentie met dezelfde woning op Funda, Pararius of de site van een erkende makelaar.';
            $checks[] = 'Controleer of het domein van de link bij een bekende verhuurplatform past (let op typfouten in de URL).';
        } else {
            $checks[] = 'Zoek een deel van de advertentietekst online — scams kopiëren vaak teksten.';
        }

        $flagLine = implode(' ', $ruleScam['flags']);
        if (preg_match('/WhatsApp|Telegram|Signal|WeChat|Skype|chat-app/i', $flagLine)) {
            $checks[] = 'Vraag een vast telefoonnummer of e-mail van een bedrijfsdomein; wees wantrouwig bij alleen chat-apps zonder traceerbaar kanaal.';
        }

        if ($data->price && $market['difference_percent'] !== null && $market['difference_percent'] < -25) {
            $checks[] = 'Vergelijk de prijs met vergelijkbare woningen in dezelfde buurt (minimaal 3 referenties).';
        }

        $checks[] = 'Betaal nooit voor een “reservering” via Western Union, giftcards of crypto.';
        $checks[] = 'Vraag schriftelijk om legitimatie (ID) en bewijs van verhuurrecht voordat je een storting doet.';

        $cityLine = $market['average']
            ? sprintf(
                'Benchmark huur (model): €%s/maand%s. ',
                number_format($market['average'], 0, ',', '.'),
                $market['difference_percent'] !== null
                    ? sprintf('; afwijking t.o.v. jouw prijs: %d%%.', $market['difference_percent'])
                    : '.'
            )
            : '';

        $marketContext = $cityLine.'De benchmark is een vereenvoudigde schatting per stad/regio en geen taxatierapport.';

        $methodology = 'Stap 1 — invoer: uit je plaktekst of URL halen we prijs, contact en beschrijving (geen “geheime” bronnen). '
            .'Stap 2 — stad & benchmark: we proberen een stad te herkennen en vergelijken met een vaste model-benchmark (€/maand) per stad; elders geldt een standaard. Dat is een grove schatting, geen taxatie. '
            .'Stap 3 — regels: de regel-score telt meetbare signalen op (max. 100): '
            .'prijs ruim onder benchmark +30; WhatsApp +10; Telegram/Signal/WeChat/Skype +8; '
            .'urgentie (o.a. “vandaag”, “snel”, “beperkte tijd”, “veel interesse”) +20; '
            .'hoog-risico betalen (Western Union, crypto, cadeaukaarten, vooruitbetaling, enz.) +40; '
            .'geen bezichtiging / buitenland / sleutel per post of sleutelservice +12; ID vóór afspraak +10; geld vóór bezichtiging +14; '
            .'extern aanmeldformulier (Google Forms, Typeform, …) +10; klassiek emotioneel buitenland-verhaal +10; Engelstalige scam-sjabloonzinnen +8. '
            .'Stap 4 — optioneel AI: tweede risicoscore; eindscore = maximum van regel-score en AI-score. Zonder AI blijft de eindscore gelijk aan de regel-score. '
            .'Dit is geen juridisch of financieel advies en geen garantie tegen fraude.';

        return [
            'recommendations' => array_values(array_unique($recommendations)),
            'what_to_verify' => array_values(array_unique($checks)),
            'market_context' => $marketContext,
            'methodology' => $methodology,
        ];
    }
}
