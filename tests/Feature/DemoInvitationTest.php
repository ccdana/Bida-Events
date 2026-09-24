<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\GuestContribution;
use Database\Seeders\ShowcaseInvitationsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_showcase_invitations_open_as_an_interactive_demo_without_saving_anything(): void
    {
        $this->seed(ShowcaseInvitationsSeeder::class);
        $guests = Guest::count();
        $contributions = GuestContribution::count();

        $this->withoutVite()
            ->get(route('invitation.demo', 'boda-camila-andres'))
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            // Las respuestas se simulan en el navegador y la música no arranca sola
            ->assertSee('window.invDemo = true', false)
            ->assertSee('window.invCoverAutoplay = false', false)
            // Un invitado ficticio deja probar el enlace personal, la confirmación y el pase QR
            ->assertSee('Familia Pérez')
            ->assertSee('id="rsvp"', false)
            ->assertSee('x-ref="demoQr"', false)
            ->assertSee('data-cover-trigger', false);

        $this->assertSame($guests, Guest::count());
        $this->assertSame($contributions, GuestContribution::count());
    }

    public function test_the_hero_phone_version_opens_the_cover_on_its_own(): void
    {
        $this->seed(ShowcaseInvitationsSeeder::class);

        $this->withoutVite()
            ->get(route('invitation.demo', ['slug' => 'xv-isabella', 'portada' => 1]))
            ->assertOk()
            ->assertSee('window.invCoverAutoplay = true', false)
            // La apertura de la portada se muestra genérica, sin invitado
            ->assertDontSee('Familia Pérez')
            ->assertDontSee('id="rsvp"', false);
    }

    public function test_only_configured_showcase_invitations_have_a_demo(): void
    {
        $this->seed(ShowcaseInvitationsSeeder::class);
        config(['bida.demo_invitations' => ['xv-isabella'], 'bida.landings' => [], 'bida.seasons.amor.templates' => [], 'bida.seasons.halloween.templates' => [], 'bida.professionals.demos' => []]);

        $this->get(route('invitation.demo', 'boda-camila-andres'))->assertNotFound();
        $this->get(route('invitation.demo', 'tarjeta-ana-luis'))->assertNotFound();

        // La tarjeta de temporada tiene su muestra aunque no esté entre las de la portada
        config(['bida.seasons.amor.templates' => ['tarjeta-ana-luis']]);

        $this->withoutVite()->get(route('invitation.demo', ['slug' => 'tarjeta-ana-luis', 'portada' => 1]))->assertOk();
    }

    public function test_real_invitations_keep_their_normal_behavior(): void
    {
        $this->seed(ShowcaseInvitationsSeeder::class);

        $this->withoutVite()
            ->get(route('invitation.show', 'xv-isabella'))
            ->assertOk()
            ->assertSee('window.invDemo = false', false)
            ->assertDontSee('Familia Pérez');
    }
}
