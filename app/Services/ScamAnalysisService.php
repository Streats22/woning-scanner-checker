<?php

namespace App\Services;

use App\Data\ParsedListingInput;

/**
 * Regel-engine: meetbare tekstpatronen die vaak voorkomen bij misleidende huuradvertenties,
 * plus enkele subtielere signalen (lagere weging) die minder vaak maar wél bij geavanceerde scams voorkomen.
 */
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
        $t = $data->description;
        $lower = mb_strtolower($t, 'UTF-8');

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

        if (str_contains($lower, 'whatsapp')) {
            $score += 10;
            $flags[] = 'WhatsApp genoemd als contact';
            $breakdown[] = [
                'category' => 'Contactkanaal',
                'points' => 10,
                'detail' => 'WhatsApp wordt genoemd — veel scams vermijden traceerbare kanalen (e-mail/vast nummer).',
            ];
        }

        if (preg_match('/\b(telegram|signal|wechat|skype)\b/iu', $t)) {
            $score += 8;
            $flags[] = 'Alternatieve chat-app (Telegram/Signal/WeChat/Skype)';
            $breakdown[] = [
                'category' => 'Contactkanaal',
                'points' => 8,
                'detail' => 'Er wordt een alternatieve chat-app genoemd naast of in plaats van regulier contact — past bij anonieme communicatie.',
            ];
        }

        if (preg_match(
            '/alleen vandaag|snel reageren|tijdelijk beschikbaar|laatste kans|nu reageren|direct beschikbaar|first come|vandaag nog|beperkte tijd|binnen 24 uur|binnen 48 uur|urgent\b|direct contract|nu beslissen|eenmalige aanbieding|enorm veel interesse|veel belangstelling|honderd reacties|veel reacties|fear of missing|fomo/i',
            $t
        )) {
            $score += 20;
            $flags[] = 'Urgente verkoopdruk (scam patroon)';
            $breakdown[] = [
                'category' => 'Druk & urgentie',
                'points' => 20,
                'detail' => 'Tekst creëert tijdsdruk (o.a. “vandaag”, “snel”, “beperkte tijd”) — veel gebruikt om kritisch nadenken te verminderen.',
            ];
        }

        if ($this->matchesHighRiskPayment($lower)) {
            $score += 40;
            $flags[] = 'Hoog-risico betaalpatroon';
            $breakdown[] = [
                'category' => 'Betalingswijze',
                'points' => 40,
                'detail' => 'Vermelding van Western Union, MoneyGram, vooruitbetaling zonder zicht op woning, crypto-tegoed, cadeaukaarten of vergelijkbare moeilijk terug te halen routes.',
            ];
        }

        if (preg_match(
            '/geen bezichtiging|bezichtiging niet mogelijk|niet mogelijk om te bezichtigen|geen bezichtigingen|alleen via video|videocall i\.p\.v|sleutel per post|sleutel opsturen|sleutel met koerier|sleutelservice|sleutel via (dhl|postnl|ups|pakket)|beveiligde sleutel|in het buitenland.*werk|werk.*buitenland|woon in het buitenland|buiten nederland wonend|niet in nederland wonen/i',
            $t
        )) {
            $score += 12;
            $flags[] = 'Geen echte bezichtiging of afstands-verhaal';
            $breakdown[] = [
                'category' => 'Bezichtiging & vertrouwen',
                'points' => 12,
                'detail' => 'Geen (normale) bezichtiging, sleutel per post/koerier/sleutelservice of een verhaal over verhuur vanuit het buitenland — veel voorkomend bij oplichting.',
            ];
        }

        if (preg_match(
            '/(stuur|mail|whatsapp|app).{0,35}(paspoort|identiteitsbewijs|id-kaart|id kaart).{0,45}(voor|eerd|eerst|alvorens|vóór|voordat|meteen)/iu',
            $t
        )) {
            $score += 10;
            $flags[] = 'Identiteitsbewijs vragen vóór afspraak (risico identiteitsfraude)';
            $breakdown[] = [
                'category' => 'Identiteit & privacy',
                'points' => 10,
                'detail' => 'Verzoek om een kopie ID/paspoort vóór een bezichtiging of contract — komt voor bij phishing en identiteitsfraude; betrouwbare partijen vragen dit meestal in een later, afgesloten stadium.',
            ];
        }

        if (preg_match(
            '/(kopie|foto).{0,25}(paspoort|identiteitsbewijs|id-kaart).{0,55}(uitwisselen|wisselen|ruilen|delen).{0,40}(vertrouw|bewijs)/iu',
            $t
        )) {
            $score += 10;
            $flags[] = 'ID uitwisselen met onbekende verhuurder (studenten-/kamer-scam)';
            $breakdown[] = [
                'category' => 'Identiteit & privacy',
                'points' => 10,
                'detail' => 'Patroon van “ID’s uitwisselen” met iemand die je niet kent — expliciet genoemd als risico bij kamerfraude; gebruik liever de Kopie-ID-app en geen onbeschermde kopie via chat.',
            ];
        }

        if (preg_match(
            '/(bezichtigingskosten|inschrijfgeld|reserveringskosten|administratiekosten|sleutelgeld|borg|bemiddelingskosten|commissiekosten|contractkosten|advieskosten|eenmalige kosten).{0,80}(vooraf|voordat|vóór|voor de rondleiding|voor een afspraak|om te reserveren)|vooraf.{0,40}(bezichtiging|reservering|inschrijving)|\b(bemiddelingskosten|commissiekosten|inschrijfgeld)\b.{0,50}(student|kamer|kamers|huur)/iu',
            $t
        )) {
            $score += 14;
            $flags[] = 'Geld vragen vóór bezichtiging of reservering';
            $breakdown[] = [
                'category' => 'Vooraf betalen',
                'points' => 14,
                'detail' => 'Er wordt om geld gevraagd vóór een bezichtiging, “reservering” of onder valse naam (bemiddelings-/contract-/advieskosten) — bij studentenkamers vaak gecombineerd met inschrijfgeld. Controleer of dit wettelijk/sector past en nooit via anonieme betaalroutes.',
            ];
        }

        if (preg_match('/forms\.gle|google\.com\/forms|typeform\.com|jotform\.com|surveymonkey\.com/i', $t)) {
            $score += 10;
            $flags[] = 'Aanmelding via extern webformulier';
            $breakdown[] = [
                'category' => 'Aanmeldproces',
                'points' => 10,
                'detail' => 'Doorverwijzing naar een generiek formulier (Google Forms, Typeform, …) — komt voor bij massale phishing en gekloonde advertenties.',
            ];
        }

        if (preg_match(
            '/(wegens|vanwege).{0,20}(verhuizing|verhuis).{0,30}(buitenland|overzees|engeland|spanje|frankrijk|duitsland)|(erfenis|overleden|nalatenschap).{0,40}(woning|huis|appartement)|missionaris|goed doel|goede doelen/i',
            $t
        )) {
            $score += 10;
            $flags[] = 'Klassiek emotioneel / buitenland-verhaal';
            $breakdown[] = [
                'category' => 'Verhaallijn',
                'points' => 10,
                'detail' => 'Patronen zoals verhuizing naar het buitenland, erfenis of “goed doel” — vaker gebruikt in copy-paste scamteksten dan in standaard NL-huurdersadvertenties.',
            ];
        }

        if (preg_match(
            '/\b(kind regards|dear sir|dear madam|respectfully|god bless|western union representative|kindly send)\b/i',
            $t
        )) {
            $score += 8;
            $flags[] = 'Engelse sjabloonfrase (internationale scam)';
            $breakdown[] = [
                'category' => 'Taal & stijl',
                'points' => 8,
                'detail' => 'Formele Engelstalige standaardzinnen die vaak voorkomen in internationale oplichtingsmails, minder in echte Nederlandse particuliere advertenties.',
            ];
        }

        return [
            'score' => min($score, 100),
            'flags' => $flags,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Veelgebruikte én minder voor de hand liggende routes; één treffer volstaat voor deze categorie.
     */
    private function matchesHighRiskPayment(string $lower): bool
    {
        if (preg_match(
            '/western union|moneygram|vooruit ?betal|vooruitbetaling|betaal voordat|betaalt u eerst|alleen crypto|bitcoin|btc\b|ethereum|eth\b|usdt|tether|paysafecard|paysafe|steam.?tegoed|steam wallet|itunes|apple.?tegoed|cadeaukaart|gift ?card|pinnen bij|postwissel|swift.?overschrijving naar particulier/iu',
            $lower
        )) {
            return true;
        }

        return (bool) preg_match(
            '/\bideal\b.{0,40}(onbekend|privérekening|particulier|niet.?nederland)|niet via de bank maar via/iu',
            $lower
        );
    }
}
