<?php

namespace App\Support;

/**
 * Wettelijke huurgrenzen 2026 (prijspeil 1 januari 2026) voor regel-indicatoren — geen juridisch advies.
 *
 * Bronnen: Huurcommissie / Rijksoverheid (Wet betaalbare huur, Wet goed verhuurderschap, Wet vaste huurcontracten).
 */
final class RentRegulation
{
    /** Maximale kale huur bij 143 punten (bovengrens sociale huur) en huurtoeslaggrens 2026. */
    public const SOCIAL_RENT_MAX_EUR = 932.93;

    /** Maximale kale huur bij 186 punten: bovengrens gereguleerde middenhuur = liberalisatiegrens 2026. */
    public const MID_RENT_MAX_EUR = 1228.07;

    /** WWS-punten: t/m 143 sociaal, 144–186 middenhuur, vanaf 187 vrije sector. */
    public const SOCIAL_POINTS_MAX = 143;

    public const MID_POINTS_MAX = 186;

    /** Wet goed verhuurderschap: waarborgsom maximaal twee maanden kale huur. */
    public const MAX_DEPOSIT_MONTHS = 2;

    /** Geplande minimumnorm: huurwoningen met label E/F/G moeten vanaf 1 januari 2029 minimaal label D hebben. */
    public const MINIMUM_LABEL_D_FROM_YEAR = 2029;

    public static function formatEur(float $amount): string
    {
        return number_format($amount, 2, ',', '.');
    }
}
