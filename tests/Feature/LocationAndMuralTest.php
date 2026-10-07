<?php

namespace Tests\Feature;

use App\Models\InvitationLocation;
use App\Services\InvitationModuleService;
use App\Support\InvitationTemplates;
use Database\Seeders\BodaJardinDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * El botón «Cómo llegar» siempre abre un mapa, aunque el campo del enlace tenga solo coordenadas,
 * y el fotomural abre la cámara del celular directo.
 */
class LocationAndMuralTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    private function invitationWithLocation(array $ubicacion): string
    {
        $invitation = $this->createInvitation(['slug' => 'ubicacion-prueba', 'template' => InvitationTemplates::BODA_JARDIN]);
        $modules = BodaJardinDemoSeeder::modules();
        $modules['ubicacion'] = array_merge(['nombre_lugar' => 'Salón Aurora'], $ubicacion);
        app(InvitationModuleService::class)->syncAllModules($invitation, $modules);

        return route('invitation.show', $invitation->slug);
    }

    public function test_coordinates_typed_in_the_link_field_open_google_maps(): void
    {
        $url = $this->invitationWithLocation(['maps_url' => '-17.39558,-66.16388']);

        $this->withoutVite()->get($url)
            ->assertOk()
            ->assertSee('href="https://www.google.com/maps?q=-17.39558,-66.16388"', false)
            ->assertSee('maps?q=-17.39558,-66.16388&amp;z=15&amp;output=embed', false)
            ->assertDontSee('href="-17.39558,-66.16388"', false);
    }

    public function test_invitations_saved_before_the_fix_with_bare_coordinates_also_open_a_map(): void
    {
        $url = $this->invitationWithLocation(['lat' => -16.5, 'lng' => -68.15]);
        InvitationLocation::query()->update(['map_url' => '-17.39558,-66.16388']);
        Cache::flush();

        $this->withoutVite()->get($url)
            ->assertOk()
            ->assertSee('href="https://www.google.com/maps/dir/?api=1&amp;destination=-17.39558,-66.16388"', false)
            ->assertDontSee('href="-17.39558,-66.16388"', false);
    }

    public function test_the_saved_link_is_a_real_maps_link(): void
    {
        $url = $this->invitationWithLocation(['maps_url' => '-17.39558,-66.16388', 'lat' => -16.5, 'lng' => -68.15]);

        $this->withoutVite()->get($url)->assertOk();

        $saved = InvitationLocation::query()->first();
        $this->assertSame('https://www.google.com/maps?q=-17.39558,-66.16388', $saved->map_url);
        $this->assertEqualsWithDelta(-17.39558, $saved->latitude, 0.00001);
    }

    public function test_a_google_maps_link_is_kept_for_the_button(): void
    {
        $url = $this->invitationWithLocation(['maps_url' => 'https://maps.app.goo.gl/abc123', 'lat' => -17.4, 'lng' => -66.1]);

        $this->withoutVite()->get($url)
            ->assertOk()
            ->assertSee('href="https://maps.app.goo.gl/abc123"', false);
    }

    public function test_the_mural_button_opens_the_phone_camera_and_the_gallery_stays_available(): void
    {
        $url = $this->invitationWithLocation(['maps_url' => '-17.39558,-66.16388']);

        $this->withoutVite()->get($url)
            ->assertOk()
            ->assertSee('x-ref="cameraInput" accept="image/*" capture="environment"', false)
            ->assertSee('x-ref="galleryInput" accept="image/*"', false)
            ->assertSee('Elegir de la galería');
    }
}
