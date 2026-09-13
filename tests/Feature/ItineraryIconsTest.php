<?php

namespace Tests\Feature;

use App\Support\ItineraryIcons;
use Tests\TestCase;

class ItineraryIconsTest extends TestCase
{
    public function test_legacy_icon_names_keep_a_matching_catalog_icon(): void
    {
        $this->assertSame('recepcion', ItineraryIcons::resolve('users'));
        $this->assertSame('velas', ItineraryIcons::resolve('candle'));
        $this->assertSame('vals', ItineraryIcons::resolve('dance'));
        $this->assertSame('cena', ItineraryIcons::resolve('dinner'));
        $this->assertSame('fiesta', ItineraryIcons::resolve('music'));
        $this->assertSame('especial', ItineraryIcons::resolve('star'));
    }

    public function test_unknown_or_empty_icons_fall_back_to_the_default(): void
    {
        $this->assertSame(ItineraryIcons::DEFAULT, ItineraryIcons::resolve(null));
        $this->assertSame(ItineraryIcons::DEFAULT, ItineraryIcons::resolve(''));
        $this->assertSame(ItineraryIcons::DEFAULT, ItineraryIcons::resolve('no-existe'));
        $this->assertSame('Vals', ItineraryIcons::label('dance'));
    }

    public function test_every_catalog_icon_has_its_own_drawing(): void
    {
        $fallback = view('invitations.partials.itinerary-icon', ['name' => ItineraryIcons::DEFAULT])->render();

        foreach (array_keys(ItineraryIcons::all()) as $key) {
            $markup = view('invitations.partials.itinerary-icon', ['name' => $key])->render();

            $this->assertStringContainsString('<svg', $markup);

            if ($key !== ItineraryIcons::DEFAULT) {
                $this->assertNotSame($fallback, $markup, "El ícono «{$key}» no tiene dibujo propio.");
            }
        }
    }
}
