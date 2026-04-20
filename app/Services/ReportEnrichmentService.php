<?php

namespace App\Services;

use App\Data\ParsedListingInput;
use App\Support\ListingDwellingRecommendationHints;

class ReportEnrichmentService
{
    public function __construct(
        private ListingDwellingClassifier $dwellingClassifier,
    ) {}

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
        $dwelling = $this->dwellingClassifier->classify($data);

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

        $cheapVsBenchmark = $data->price && $market['difference_percent'] !== null && $market['difference_percent'] < -25;
        if ($cheapVsBenchmark && $dwelling['kind'] !== 'room') {
            $checks[] = 'Vergelijk de prijs met vergelijkbare woningen in dezelfde buurt (minimaal 3 referenties).';
        }
        if ($cheapVsBenchmark && $dwelling['kind'] === 'room' && $market['difference_percent'] < -65) {
            $checks[] = 'Vergelijk deze kamerprijs met vergelijkbare kamers in dezelfde buurt (minimaal 3 referenties) — een grote afwijking t.o.v. een hele-woning-benchmark is voor kamers vaak normaal.';
        }

        $checks[] = 'Betaal nooit voor een “reservering” via Western Union, giftcards of crypto.';
        $checks[] = 'Vraag schriftelijk om legitimatie (ID) en bewijs van verhuurrecht voordat je een storting doet.';

        foreach (ListingDwellingRecommendationHints::contextualVerifyChecks($dwelling) as $hint) {
            $checks[] = $hint;
        }

        $cityLine = $market['average']
            ? sprintf(
                'Benchmark huur (model): €%s/maand%s. ',
                number_format($market['average'], 0, ',', '.'),
                $market['difference_percent'] !== null
                    ? sprintf('; afwijking t.o.v. jouw prijs: %d%%.', $market['difference_percent'])
                    : '.'
            )
            : '';

        $roomBenchNote = $dwelling['kind'] === 'room'
            ? 'Voor een kamer is een veel lagere huur dan dit model (gemiddelde hele woning in de gemeente) gebruikelijk; dat zegt op zich weinig over betrouwbaarheid. '
            : '';

        $marketContext = $cityLine.$roomBenchNote.'De benchmark is een vereenvoudigde schatting per stad/regio en geen taxatierapport.';

        $methodology = "Stap 1 — invoer: uit je plaktekst of URL halen we prijs, contact en beschrijving (geen “geheime” bronnen). Zo mogelijk lezen we ook oppervlakte (m²) uit de tekst.\n\n"
            ."Stap 1b — type woning: we lezen de tekst en link en schatten of het om een kamer of een hele woning/studio gaat, en of het waarschijnlijk particuliere huur, sociale huur of onbekend is. Dat is een automatische inschatting op veelvoorkomende woorden en URL-patronen — geen juridische kwalificatie.\n\n"
            ."Stap 1c (informatief): we geven apart aan hoe sterk de tekst op een huuradvertentie lijkt — dat wijzigt de risicoscore niet.\n\n"
            ."Stap 2 — stad & benchmark: we proberen een gemeente te herkennen (tekst en URL-pad; o.a. komma-regels zoals “straat, plaats”) en vergelijken met een vaste model-benchmark (€/maand) per gemeente; elders geldt een standaard. Dat is een grove schatting, geen taxatie.\n\n"
            ."Stap 2b — prijs per m² (alleen als prijs én m² uit de tekst komen): we tonen een indicatieve €/m² naast het model en een band die voor kleine oppervlaktes hoger mag zijn — nog steeds geen taxatie.\n\n"
            ."Stap 3 — regels: de regel-score telt meetbare signalen op (max. 100):\n\n"
            ."Prijs & urgentie\n"
            ."– prijs ruim onder benchmark +30 (voor een kamer: alleen bij opvallend laag t.o.v. hetzelfde model, want kamers liggen normaal veel onder een gemiddelde voor een hele woning)\n"
            ."– WhatsApp +10\n"
            ."– Telegram/Signal/WeChat/Skype +8\n"
            ."– urgentie (o.a. “vandaag”, “snel”, “beperkte tijd”, “veel interesse”) +20\n\n"
            ."Betaling, bezichtiging & vertrouwen\n"
            ."– hoog-risico betalen (Western Union, crypto, cadeaukaarten, vooruitbetaling, enz.) +40\n"
            ."– geen bezichtiging / buitenland / sleutel per post of sleutelservice +12\n"
            ."– identiteit/privacy: o.a. ID vóór afspraak +10; “ID uitwisselen”-patroon +10 (elk kan afzonderlijk meetellen)\n"
            ."– geld vóór bezichtiging +14\n\n"
            ."Formulieren & taal\n"
            ."– extern aanmeldformulier (Google Forms, Typeform, …) +10\n"
            ."– klassiek emotioneel buitenland-verhaal +10\n"
            ."– Engelstalige scam-sjabloonzinnen +8\n\n"
            ."Stap 4 — optioneel AI: tweede risicoscore; eindscore = maximum van regel-score en AI-score. Zonder AI blijft de eindscore gelijk aan de regel-score.\n\n"
            .'Dit is geen juridisch of financieel advies en geen garantie tegen fraude.';

        return [
            'recommendations' => array_values(array_unique($recommendations)),
            'what_to_verify' => array_values(array_unique($checks)),
            'market_context' => $marketContext,
            'methodology' => $methodology,
        ];
    }
}
