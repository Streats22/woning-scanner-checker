<?php

namespace App\Services;

use App\Data\ParsedListingInput;
use App\Support\RentPerSquareMeterModel;
use App\Support\RentRegulation;

/**
 * Regel-engine: meetbare tekstpatronen die vaak voorkomen bij misleidende huuradvertenties,
 * plus enkele subtielere signalen (lagere weging) die minder vaak maar wél bij geavanceerde scams voorkomen.
 *
 * Alle zichtbare teksten via lang/scam.php — afhankelijk van app-locale (POST /api/analyze: locale).
 */
class ScamAnalysisService
{
    /**
     * Prijs t.o.v. gemeentelijke model-benchmark (€/maand, hele woning, zonder bekende m²).
     * Onder deze fractie telt “ruim onder benchmark” mee voor de score.
     */
    private const BENCHMARK_PRICE_RATIO_WHOLE_OR_UNKNOWN = 0.7;

    /**
     * Met bekende oppervlakte: €/m² zoveel procent (of meer) onder de voor grootte gecorrigeerde €/m²-benchmark.
     * Ruimer dan de maandgrens, omdat gereguleerde middenhuur (t/m € 1.228,07) legitiem ver onder de vrije sector kan liggen.
     */
    private const PER_M2_BELOW_BENCHMARK_PERCENT = -45;

    /** Kamers: prijs onder deze fractie van de mediane kamerhuur in de gemeente (Kamernet). */
    private const ROOM_PRICE_RATIO = 0.55;

    /**
     * Kamers zonder kamer-benchmark (oude snapshots): fractie van het hele-woning-model.
     */
    private const BENCHMARK_PRICE_RATIO_ROOM = 0.27;

    /** Landcodes voor buitenlandse IBAN's die in huurfraude terugkomen (NL bewust uitgesloten). */
    private const FOREIGN_IBAN_COUNTRIES = 'AT|BE|BG|CH|CY|CZ|DE|DK|EE|ES|FI|FR|GB|GR|HR|HU|IE|IT|LT|LU|LV|MT|NO|PL|PT|RO|SE|SI|SK|TR';

    public function __construct(
        private ListingDwellingClassifier $dwellingClassifier,
    )
    {
    }

    /**
     * @param  array{average: int, difference_percent: ?int, room_average?: int}  $market
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

        $dwelling = $this->dwellingClassifier->classify($data);

        $detail = $this->priceBelowMarketDetail($data, $market, $dwelling);
        if ($detail !== null) {
            $score += 30;
            $flags[] = __('scam.flags.price_below_market');
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
            '/alleen vandaag|snel reageren|tijdelijk beschikbaar|laatste kans|nu reageren|direct beschikbaar|first come|vandaag nog|beperkte tijd|binnen 24 uur|binnen 48 uur|urgent\b|direct contract|nu beslissen|eenmalige aanbieding|enorm veel interesse|veel belangstelling|honderd reacties|veel reacties|andere kandidaten|andere gegadigden|other candidates|vandaag (nog )?betalen|fear of missing|fomo/i',
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

        if ($this->matchesPlatformPaymentProxy($lower)) {
            $score += 35;
            $flags[] = __('scam.flags.platform_payment');
            $breakdown[] = [
                'category' => __('scam.categories.payment'),
                'points' => 35,
                'detail' => __('scam.breakdown.platform_payment_detail'),
            ];
        }

        if ($this->matchesForeignAccount($t)) {
            $score += 20;
            $flags[] = __('scam.flags.foreign_account');
            $breakdown[] = [
                'category' => __('scam.categories.payment'),
                'points' => 20,
                'detail' => __('scam.breakdown.foreign_account_detail'),
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
            '/geen bezichtiging|bezichtiging niet mogelijk|niet mogelijk om te bezichtigen|geen bezichtigingen|alleen via video|videocall i\.p\.v|sleutel per post|sleutel opsturen|sleutel met koerier|sleutelservice|sleutel via (dhl|postnl|ups|pakket)|sleutels? (wordt|worden) (op|toe)?gestuurd|(wordt|worden) de sleutels? (op|toe)?gestuurd|keys? (will be |are )?(sent|mailed|shipped|posted)|beveiligde sleutel|in het buitenland.*werk|werk.*buitenland|woon in het buitenland|buiten nederland wonend|niet in nederland wonen/i',
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
            '/(bezichtigingskosten|inschrijfgeld|reserveringskosten|administratiekosten|sleutelgeld|borg|bemiddelingskosten|commissiekosten|contractkosten|advieskosten|eenmalige kosten).{0,80}(vooraf|voordat|vóór|voor de rondleiding|voor een afspraak|om te reserveren)|vooraf.{0,40}(bezichtiging|reservering|inschrijving)|\b(bezichtigingskosten|bezichtigingsfee|viewing fee|reserveringsgarantie|reserveringsfee|reservation fee)\b|eerst betalen|betaal (eerst|vooraf)|na (de )?betaling (krijg|ontvang|stuur|volgt)|(bewijs van|proof of) (betaling|payment)|\bbetaalbewijs\b|\b(bemiddelingskosten|commissiekosten|inschrijfgeld)\b.{0,50}(student|kamer|kamers|huur)/iu',
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

        $deposit = $this->excessiveDepositMonths($t, $data->price);
        if ($deposit !== null) {
            $score += 20;
            $flags[] = __('scam.flags.deposit_too_high');
            $breakdown[] = [
                'category' => __('scam.categories.legal_costs'),
                'points' => 20,
                'detail' => __('scam.breakdown.deposit_too_high_detail', [
                    'months' => number_format($deposit, 1, ',', '.'),
                    'max' => RentRegulation::MAX_DEPOSIT_MONTHS,
                ]),
            ];
        }

        if ($this->chargesTenantFees($lower)) {
            $score += 10;
            $flags[] = __('scam.flags.tenant_fees');
            $breakdown[] = [
                'category' => __('scam.categories.legal_costs'),
                'points' => 10,
                'detail' => __('scam.breakdown.tenant_fees_detail'),
            ];
        }

        if (preg_match(
            '/(verifi\w*|bevestig\w*|confirm\w*|controleer\w*).{0,30}(betaal|bank|creditcard|kaart)gegevens|(verify|confirm) (your )?(payment|card|bank) (details|information)|(verifieer|verifiëer|bevestig) (je|uw) (boeking|account|reservering) (via|binnen|door|met)|verify your (booking|account)|account (wordt |is )?(geblokkeerd|opgeschort)|verlengingskosten/iu',
            $t
        )) {
            $score += 15;
            $flags[] = __('scam.flags.verification_phishing');
            $breakdown[] = [
                'category' => __('scam.categories.signup_process'),
                'points' => 15,
                'detail' => __('scam.breakdown.verification_phishing_detail'),
            ];
        }

        if (preg_match('/\b(stuur|send)\s+(me|mij|ons|us)?\s*(een|a)?\s*dm\b|\bdm\s+(me|mij|ons)\b|link in (de |my |mijn )?bio\b/iu', $t)) {
            $score += 6;
            $flags[] = __('scam.flags.social_dm');
            $breakdown[] = [
                'category' => __('scam.categories.contact_channel'),
                'points' => 6,
                'detail' => __('scam.breakdown.social_dm_detail'),
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
     * @param  array{average: int, difference_percent: ?int, room_average?: int}  $market
     * @param  array{kind: string, rental_sector: string}  $dwelling
     */
    private function priceBelowMarketDetail(ParsedListingInput $data, array $market, array $dwelling): ?string
    {
        if (! $data->price) {
            return null;
        }

        $priceFmt = number_format((float) $data->price, 0, ',', '.');
        $avgFmt = number_format((float) $market['average'], 0, ',', '.');

        if ($dwelling['kind'] === 'room') {
            if (isset($market['room_average']) && $market['room_average'] > 0) {
                return $data->price < $market['room_average'] * self::ROOM_PRICE_RATIO
                    ? __('scam.breakdown.price_detail_room_median', [
                        'price' => $priceFmt,
                        'room_average' => number_format((float) $market['room_average'], 0, ',', '.'),
                    ])
                    : null;
            }

            return $data->price < $market['average'] * self::BENCHMARK_PRICE_RATIO_ROOM
                ? __('scam.breakdown.price_detail_room', ['price' => $priceFmt, 'average' => $avgFmt])
                : null;
        }

        /* Corporatiewoningen liggen per definitie onder de vrije-sectorbenchmark. */
        if ($dwelling['rental_sector'] === 'social') {
            return null;
        }

        if ($data->surfaceM2 !== null && $data->surfaceM2 > 0) {
            $perM2 = RentPerSquareMeterModel::build($data->price, $data->surfaceM2, $market['average']);
            if ($perM2['per_m2_vs_adjusted_percent'] === null) {
                return null;
            }

            return $perM2['per_m2_vs_adjusted_percent'] <= self::PER_M2_BELOW_BENCHMARK_PERCENT
                ? __('scam.breakdown.price_detail_per_m2', [
                    'price' => $priceFmt,
                    'surface' => number_format($data->surfaceM2, 0, ',', '.'),
                    'price_m2' => $perM2['price_per_m2_month_eur'],
                    'benchmark_m2' => number_format((float) $perM2['adjusted_benchmark_per_m2_month_eur'], 0, ',', '.'),
                    'pct' => abs($perM2['per_m2_vs_adjusted_percent']),
                ])
                : null;
        }

        /*
         * Zonder m² hooguit tot de sociale huurgrens: daarboven kan het om legale gereguleerde middenhuur gaan
         * (Wet betaalbare huur), die in dure steden ver onder het vrije-sectormodel ligt.
         */
        $threshold = min($market['average'] * self::BENCHMARK_PRICE_RATIO_WHOLE_OR_UNKNOWN, RentRegulation::SOCIAL_RENT_MAX_EUR);

        return $data->price < $threshold
            ? __('scam.breakdown.price_detail_whole', ['price' => $priceFmt, 'average' => $avgFmt])
            : null;
    }

    /**
     * Betalen “via Airbnb/Booking” voor een gewone huurwoning: nep-betaalpagina’s van bekende platforms.
     */
    private function matchesPlatformPaymentProxy(string $lower): bool
    {
        return (bool) preg_match(
            '/(beta+l\w*|payment|pay|boek\w*|book\w*|reserve\w*)\s.{0,30}\b(via|through|met|over|on)\s+(airbnb|booking(\.com)?|vrbo|homeaway)\b|(airbnb|booking\.com|vrbo)\s*[-.]?\s*(link|betaalpagina|betaallink|payment( link| page)?|beveiligde betaling|secure payment)/iu',
            $lower
        );
    }

    /**
     * Buitenlandse IBAN (expliciet nummer) of “buitenlandse rekening” — geld lastig terug te halen.
     */
    private function matchesForeignAccount(string $text): bool
    {
        if (preg_match('/buitenlandse (bank)?rekening|foreign (bank )?account|rekening in het buitenland/iu', $text)) {
            return true;
        }

        return (bool) preg_match(
            '/\b(?:'.self::FOREIGN_IBAN_COUNTRIES.')\d{2}(?:\s?[A-Z0-9]{4}){3,7}(?:\s?[A-Z0-9]{1,3})?\b/u',
            $text
        );
    }

    /**
     * Borg boven het wettelijk maximum (2 maanden kale huur, Wet goed verhuurderschap).
     * Geeft het aantal maanden terug als het maximum wordt overschreden, anders null.
     */
    private function excessiveDepositMonths(string $text, ?int $price): ?float
    {
        $words = ['twee' => 2, 'drie' => 3, 'vier' => 4, 'vijf' => 5, 'zes' => 6, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5, 'six' => 6];
        $num = '(\d{1,2}|twee|drie|vier|vijf|zes|two|three|four|five|six)';
        $months = null;

        if (preg_match('/'.$num.'\s*(?:x|maal|keer)?\s*(?:de\s+)?(?:maand(?:en)?|months?)\s*(?:kale\s+)?(?:huur\s+|rent\s+)?(?:als\s+|as\s+)?(?:borg|waarborg|deposit)/iu', $text, $m)
            || preg_match('/(?:borg|waarborgsom|waarborg|deposit)\s*(?:van|bedraagt|is|of|:)?\s*'.$num.'\s*(?:x\s*)?(?:maal\s+|keer\s+)?(?:de\s+)?(?:maand(?:en)?|months?)/iu', $text, $m)) {
            $raw = mb_strtolower($m[1], 'UTF-8');
            $months = (float) ($words[$raw] ?? $raw);
        } elseif ($price !== null && $price > 0 && preg_match(
            '/(?:borg|waarborgsom|waarborg|deposit)\s*(?:van|bedraagt|is|of|:)?\s*(?:€|eur|euro)\s*(\d{1,2}(?:[.\s]\d{3})+|\d{3,5})(?:,\d{2})?/iu',
            $text,
            $m
        )) {
            $amount = (int) preg_replace('/\D/', '', $m[1]);
            $months = $amount / $price;
        }

        /* Kleine marge: borg “2 maanden incl. servicekosten” of afronding mag niet direct meetellen. */
        return $months !== null && $months > RentRegulation::MAX_DEPOSIT_MONTHS + 0.15 && $months <= 24
            ? round($months, 1)
            : null;
    }

    /**
     * Bemiddelings-/contractkosten voor de huurder — sinds de Wet goed verhuurderschap verboden.
     * Ontkenningen (“geen bemiddelingskosten”) tellen niet.
     */
    private function chargesTenantFees(string $lower): bool
    {
        if (! preg_match_all(
            '/\b(bemiddelingskosten|makelaarskosten|courtage|plaatsingskosten|contractkosten|administratiekosten|sleutelgeld|agency fee|agent fee)\b/u',
            $lower,
            $matches,
            PREG_OFFSET_CAPTURE
        )) {
            return false;
        }

        foreach ($matches[0] as [$word, $offset]) {
            $before = substr($lower, max(0, $offset - 40), min(40, $offset));
            $after = substr($lower, $offset + strlen($word), 40);
            if (preg_match('/\b(geen|zonder|niet|verboden|no|without|free of)\b/u', $before)
                || preg_match('/^\W{0,3}(zijn |is )?(niet|verboden|n\.v\.t|nvt|nihil|€\s*0\b|0,-)/u', $after)) {
                continue;
            }

            return true;
        }

        return false;
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
