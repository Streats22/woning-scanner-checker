<?php

namespace Tests\Unit;

use App\Support\EnergyLabel;
use PHPUnit\Framework\TestCase;

class EnergyLabelTest extends TestCase
{
    public function test_parses_common_notations(): void
    {
        $this->assertSame('A', EnergyLabel::parse('Energielabel: A'));
        $this->assertSame('A+++', EnergyLabel::parse('Mooi appartement met energielabel A+++ en zonnepanelen'));
        $this->assertSame('C', EnergyLabel::parse('Energy label C, available now'));
        $this->assertSame('G', EnergyLabel::parse('energie-label g'));
        $this->assertSame('B', EnergyLabel::parse('Energieklasse: B'));
    }

    public function test_ignores_text_without_explicit_label(): void
    {
        $this->assertNull(EnergyLabel::parse('Energielabel aanwezig, vraag ernaar'));
        $this->assertNull(EnergyLabel::parse('Appartement A-locatie, label onbekend'));
        $this->assertNull(EnergyLabel::parse(null));
    }

    public function test_poor_labels(): void
    {
        $this->assertTrue(EnergyLabel::isPoor('E'));
        $this->assertTrue(EnergyLabel::isPoor('G'));
        $this->assertFalse(EnergyLabel::isPoor('D'));
        $this->assertFalse(EnergyLabel::isPoor('A++'));
    }

    public function test_wws_points(): void
    {
        $this->assertSame(37.0, EnergyLabel::wwsPoints('A', 60.0));
        $this->assertSame(41.0, EnergyLabel::wwsPoints('A', 60.0, singleFamily: true));
        $this->assertSame(-15.0, EnergyLabel::wwsPoints('G', 60.0));
        $this->assertSame(-4.0, EnergyLabel::wwsPoints('E', null));
        $this->assertSame(45.0, EnergyLabel::wwsPoints('A', 20.0));
        $this->assertSame(62.0, EnergyLabel::wwsPoints('A++++', 30.0));
        $this->assertSame(1.3, EnergyLabel::wwsPoints('A', 2.0, room: true));
        $this->assertNull(EnergyLabel::wwsPoints('A', null, room: true));
    }
}
