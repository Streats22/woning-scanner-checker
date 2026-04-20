<?php

namespace App\Services;

use App\Data\ParsedListingInput;

/**
 * Regel-engine: meetbare tekstpatronen die vaak voorkomen bij misleidende huuradvertenties,
 * plus enkele subtielere signalen (lagere weging) die minder vaak maar wél bij geavanceerde scams voorkomen.
 *
 * Alle zichtbare teksten via lang/scam.php — afhankelijk van app-locale (POST /api/analyze: locale).
 */
class ScamAnalysisService
{
    /**
     * Prijs t.o.v. gemeentelijke model-benchmark (€/maand, hele woning).
     * Onder deze fractie telt “ruim onder benchmark” mee voor de score.
     */
    private const BENCHMARK_PRICE_RATIO_WHOLE_OR_UNKNOWN = 0.7;

    /**
     * Kamers horen vaak honderden euro’s onder een gemiddelde voor een hele woning; alleen extreem lage
     * bedragen tellen nog mee als hetzelfde signaal (smallere fractie).
     */
    private const BENCHMARK_PRICE_RATIO_ROOM = 0.27;

    public function __construct(
        private ListingDwellingClassifier $dwellingClassifier,
    ) {}

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

        $dwellingKind = $this->dwellingClassifier->classify($data)['kind'];
        $priceRatio = $dwellingKind === 'room'
            ? self::BENCHMARK_PRICE_RATIO_ROOM
            : self::BENCHMARK_PRICE_RATIO_WHOLE_OR_UNKNOWN;

        $priceFmt = $data->price ? number_format((float) $data->price, 0, ',', '.') : '';
        $avgFmt = number_format((float) $market['average'], 0, ',', '.');

        if ($data->price && $data->price < ($market['average'] * $priceRatio)) {
            $score += 30;
            $flags[] = __('scam.flags.price_below_market');
            $detail = $dwellingKind === 'room'
                ? __('scam.breakdown.price_detail_room', ['price' => $priceFmt, 'average' => $avgFmt])
                : __('scam.breakdown.price_detail_whole', ['price' => $priceFmt, 'average' => $avgFmt]);
            $breakdown[] = [
                'category' => __('scam.categories.price_vs_benchmark'),
                'points' => 30,
                'detail' => $detail,
            ];
        }

        if (str_contains($lower, 'whatsapp')) {
            $score += 10;
            $flags[] = __('scam.flags.whatsapp_contact');
            $breakdown[] = [
                'category' => __('scam.categories.contact_channel'),
                'points' => 10,
                'detail' => __('scam.breakdown.whatsapp_detail'),
            ];
        }

        if (preg_match('/\b(telegram|signal|wechat|skype)\b/iu', $t)) {
            $score += 8;
            $flags[] = __('scam.flags.alt_chat_app');
            $breakdown[] = [
                'category' => __('scam.categories.contact_channel'),
                'points' => 8,
                'detail' => __('scam.breakdown.alt_chat_detail'),
            ];
        }

        if (preg_match(
            '/alleen vandaag|snel reageren|tijdelijk beschikbaar|laatste kans|nu reageren|direct beschikbaar|first come|vandaag nog|beperkte tijd|binnen 24 uur|binnen 48 uur|urgent\b|direct contract|nu beslissen|eenmalige aanbieding|enorm veel interesse|veel belangstelling|honderd reacties|veel reacties|fear of missing|fomo/i',
            $t
        )) {
            $score += 20;
            $flags[] = __('scam.flags.urgency');
            $breakdown[] = [
                'category' => __('scam.categories.urgency'),
                'points' => 20,
                'detail' => __('scam.breakdown.urgency_detail'),
            ];
        }

        if ($this->matchesHighRiskPayment($lower)) {
            $score += 40;
            $flags[] = __('scam.flags.high_risk_payment');
            $breakdown[] = [
                'category' => __('scam.categories.payment'),
                'points' => 40,
                'detail' => __('scam.breakdown.high_risk_payment_detail'),
            ];
        }

        if (preg_match(
            '/geen bezichtiging|bezichtiging niet mogelijk|niet mogelijk om te bezichtigen|geen bezichtigingen|alleen via video|videocall i\.p\.v|sleutel per post|sleutel opsturen|sleutel met koerier|sleutelservice|sleutel via (dhl|postnl|ups|pakket)|beveiligde sleutel|in het buitenland.*werk|werk.*buitenland|woon in het buitenland|buiten nederland wonend|niet in nederland wonen/i',
            $t
        )) {
            $score += 12;
            $flags[] = __('scam.flags.no_viewing');
            $breakdown[] = [
                'category' => __('scam.categories.viewing_trust'),
                'points' => 12,
                'detail' => __('scam.breakdown.no_viewing_detail'),
            ];
        }

        if (preg_match(
            '/(stuur|mail|whatsapp|app).{0,35}(paspoort|identiteitsbewijs|id-kaart|id kaart).{0,45}(voor|eerd|eerst|alvorens|vóór|voordat|meteen)/iu',
            $t
        )) {
            $score += 10;
            $flags[] = __('scam.flags.id_before_visit');
            $breakdown[] = [
                'category' => __('scam.categories.identity_privacy'),
                'points' => 10,
                'detail' => __('scam.breakdown.id_before_detail'),
            ];
        }

        if (preg_match(
            '/(kopie|foto).{0,25}(paspoort|identiteitsbewijs|id-kaart).{0,55}(uitwisselen|wisselen|ruilen|delen).{0,40}(vertrouw|bewijs)/iu',
            $t
        )) {
            $score += 10;
            $flags[] = __('scam.flags.id_exchange');
            $breakdown[] = [
                'category' => __('scam.categories.identity_privacy'),
                'points' => 10,
                'detail' => __('scam.breakdown.id_exchange_detail'),
            ];
        }

        if (preg_match(
            '/(bezichtigingskosten|inschrijfgeld|reserveringskosten|administratiekosten|sleutelgeld|borg|bemiddelingskosten|commissiekosten|contractkosten|advieskosten|eenmalige kosten).{0,80}(vooraf|voordat|vóór|voor de rondleiding|voor een afspraak|om te reserveren)|vooraf.{0,40}(bezichtiging|reservering|inschrijving)|\b(bemiddelingskosten|commissiekosten|inschrijfgeld)\b.{0,50}(student|kamer|kamers|huur)/iu',
            $t
        )) {
            $score += 14;
            $flags[] = __('scam.flags.money_before_viewing');
            $breakdown[] = [
                'category' => __('scam.categories.advance_payment'),
                'points' => 14,
                'detail' => __('scam.breakdown.money_before_detail'),
            ];
        }

        if (preg_match('/forms\.gle|google\.com\/forms|typeform\.com|jotform\.com|surveymonkey\.com/i', $t)) {
            $score += 10;
            $flags[] = __('scam.flags.external_form');
            $breakdown[] = [
                'category' => __('scam.categories.signup_process'),
                'points' => 10,
                'detail' => __('scam.breakdown.external_form_detail'),
            ];
        }

        if (preg_match(
            '/(wegens|vanwege).{0,20}(verhuizing|verhuis).{0,30}(buitenland|overzees|engeland|spanje|frankrijk|duitsland)|(erfenis|overleden|nalatenschap).{0,40}(woning|huis|appartement)|missionaris|goed doel|goede doelen/i',
            $t
        )) {
            $score += 10;
            $flags[] = __('scam.flags.emotional_story');
            $breakdown[] = [
                'category' => __('scam.categories.storyline'),
                'points' => 10,
                'detail' => __('scam.breakdown.emotional_story_detail'),
            ];
        }

        if (preg_match(
            '/\b(kind regards|dear sir|dear madam|respectfully|god bless|western union representative|kindly send)\b/i',
            $t
        )) {
            $score += 8;
            $flags[] = __('scam.flags.english_template');
            $breakdown[] = [
                'category' => __('scam.categories.language_style'),
                'points' => 8,
                'detail' => __('scam.breakdown.english_template_detail'),
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
