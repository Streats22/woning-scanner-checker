<?php

namespace App\Support;

/**
 * Tijdelijke app-locale voor render/analyse, met herstel van de vorige locale.
 */
final class LocaleContext
{
    public static function run(string $locale, callable $callback): mixed
    {
        $app = app();
        $previous = $app->getLocale();
        $app->setLocale($locale);
        try {
            return $callback();
        } finally {
            $app->setLocale($previous);
        }
    }
}
