<?php

namespace Tests\Unit;

use App\Support\MapsLinkParser;
use PHPUnit\Framework\TestCase;

class MapsLinkParserTest extends TestCase
{
    public function test_bare_coordinates_in_the_link_field_become_a_real_directions_link(): void
    {
        $url = MapsLinkParser::directionsUrl(['maps_url' => '-17.39558,-66.16388']);

        $this->assertSame('https://www.google.com/maps/dir/?api=1&destination=-17.39558,-66.16388', $url);
        $this->assertSame(
            'https://www.google.com/maps/dir/?api=1&destination=-17.39558,-66.16388',
            MapsLinkParser::directionsUrl(['maps_url' => '-17.39558, -66.16388'])
        );
    }

    public function test_typed_coordinates_win_over_the_editor_default_point(): void
    {
        $location = ['maps_url' => '-17.39558,-66.16388', 'lat' => -16.5, 'lng' => -68.15];

        $this->assertSame(['lat' => -17.39558, 'lng' => -66.16388], MapsLinkParser::coordinates($location));
        $this->assertStringContainsString('q=-17.39558,-66.16388', MapsLinkParser::embedUrl($location));
    }

    public function test_real_map_links_are_kept_as_they_are(): void
    {
        foreach ([
            'https://maps.app.goo.gl/abc123',
            'https://goo.gl/maps/abc123',
            'https://www.google.com/maps/place/Salon+Imperial/@-16.5,-68.1,17z/data=!3d-16.5!4d-68.1',
            'https://www.google.com.bo/maps?q=-17.3,-66.1',
            'https://maps.google.com/?q=-17.3720,-66.1590',
            'https://waze.com/ul?ll=-17.3,-66.1',
        ] as $link) {
            $this->assertSame($link, MapsLinkParser::directionsUrl(['maps_url' => $link, 'lat' => 1, 'lng' => 1]), $link);
        }
    }

    public function test_a_link_pasted_without_scheme_gets_https(): void
    {
        $this->assertSame(
            'https://maps.app.goo.gl/abc123',
            MapsLinkParser::directionsUrl(['maps_url' => 'maps.app.goo.gl/abc123'])
        );
    }

    public function test_a_link_that_is_not_a_map_falls_back_to_the_saved_point(): void
    {
        $url = MapsLinkParser::directionsUrl(['maps_url' => 'https://example.com/salon', 'lat' => -17.4, 'lng' => -66.1]);

        $this->assertSame('https://www.google.com/maps/dir/?api=1&destination=-17.4,-66.1', $url);
    }

    public function test_dangerous_schemes_never_reach_the_href(): void
    {
        $this->assertNull(MapsLinkParser::directionsUrl(['maps_url' => 'javascript:alert(1)']));
        $this->assertNull(MapsLinkParser::webUrl('javascript:alert(1)'));
        $this->assertNull(MapsLinkParser::webUrl('-17.39,-66.16'));
    }

    public function test_without_coordinates_it_searches_by_name_and_address(): void
    {
        $url = MapsLinkParser::directionsUrl(['nombre_lugar' => 'Salón Aurora', 'direccion' => 'Av. Siempre Viva 123']);

        $this->assertSame('https://www.google.com/maps/search/?api=1&query='.urlencode('Salón Aurora, Av. Siempre Viva 123'), $url);
        $this->assertNull(MapsLinkParser::directionsUrl([]));
    }
}
