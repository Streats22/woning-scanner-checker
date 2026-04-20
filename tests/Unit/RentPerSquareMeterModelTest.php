<?php

namespace Tests\Unit;

use App\Support\RentPerSquareMeterModel;
use PHPUnit\Framework\TestCase;

class RentPerSquareMeterModelTest extends TestCase
{
    public function test_builds_per_m2_and_adjusted_for_small_surface(): void
    {
        $benchmarkMonthly = 1850;
        $out = RentPerSquareMeterModel::build(1200, 12.0, $benchmarkMonthly);

        $this->assertSame(12.0, $out['surface_m2']);
        $this->assertSame(100, $out['price_per_m2_month_eur']);
        $this->assertSame(34, $out['benchmark_per_m2_month_eur']);
        $this->assertNotNull($out['adjusted_benchmark_per_m2_month_eur']);
        $this->assertIsInt($out['per_m2_vs_adjusted_percent']);
        $this->assertTrue($out['small_surface']);
    }

    public function test_build_without_surface_leaves_per_m2_comparison_null(): void
    {
        $out = RentPerSquareMeterModel::build(900, null, 1200);

        $this->assertNull($out['surface_m2']);
        $this->assertNull($out['price_per_m2_month_eur']);
        $this->assertNull($out['adjusted_benchmark_per_m2_month_eur']);
        $this->assertNull($out['per_m2_vs_adjusted_percent']);
        $this->assertFalse($out['small_surface']);
    }
}
