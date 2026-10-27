<?php

namespace App\Console\Commands;

use App\Support\RentBenchmarkMap;
use Illuminate\Console\Command;

/**
 * Controlelijst: alle gemeenten in data/nl_rent_benchmarks.php met model-€/maand, €/m² en kamerhuur.
 */
class ListRentBenchmarksCommand extends Command
{
    protected $signature = 'rent:benchmarks:list {--csv : Output as CSV (municipality,eur_per_month,eur_per_m2,room_eur_per_month)}';

    protected $description = 'List every municipality model monthly rent for audit (same table as the rule engine).';

    public function handle(): int
    {
        $map = RentBenchmarkMap::benchmarks();
        ksort($map, SORT_NATURAL);

        if ($this->option('csv')) {
            $this->line('municipality,eur_per_month,eur_per_m2,room_eur_per_month');
            foreach ($map as $city => $eur) {
                $safe = '"'.str_replace('"', '""', $city).'"';
                $this->line($safe.','.$eur.','.RentBenchmarkMap::perM2For($city).','.RentBenchmarkMap::roomAverageFor($city));
            }

            return self::SUCCESS;
        }

        $this->info('Municipalities in model: '.count($map));
        $this->info('Fallback when city unknown: € '.RentBenchmarkMap::DEFAULT_AVERAGE.'/month, € '
            .RentBenchmarkMap::DEFAULT_PER_M2.'/m², room € '.RentBenchmarkMap::DEFAULT_ROOM_AVERAGE.'/month');
        $this->newLine();

        foreach ($map as $city => $eur) {
            $this->line(
                str_pad($city, 36, ' ', STR_PAD_RIGHT).' € '.str_pad((string) $eur, 5, ' ', STR_PAD_LEFT)
                .'  € '.number_format(RentBenchmarkMap::perM2For($city), 2, ',', '.').'/m²'
                .'  kamer € '.RentBenchmarkMap::roomAverageFor($city)
            );
        }

        return self::SUCCESS;
    }
}
