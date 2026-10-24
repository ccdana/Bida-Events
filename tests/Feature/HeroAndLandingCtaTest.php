<?php

namespace Tests\Feature;

use App\Support\DiyInvitations;
use Database\Seeders\ShowcaseInvitationsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * La portada tiene su acción principal a la vista (también en el celular) y las páginas por evento
 * cuentan el mismo camino que su botón: la invitación la arma cada quien, gratis.
 */
class HeroAndLandingCtaTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_hero_has_a_main_action_and_a_way_to_see_the_samples(): void
    {
        // «Ver muestras» baja a la sección de plantillas, que existe cuando hay muestras sembradas
        (new ShowcaseInvitationsSeeder)->seedSlug('xv-isabella');
        $start = DiyInvitations::startUrl(null);

        $this->withoutVite()->get('/')
            ->assertOk()
            ->assertSeeInOrder([
                'site-hero__lead',
                'site-hero__actions',
                'href="'.$start.'" class="site-btn site-btn--lg"',
                'Crear mi invitación gratis',
                // Las muestras, un círculo con una flecha (el nombre va en el aria-label)
                'href="#plantillas" class="site-btn site-btn--ghost site-btn--lg site-btn--icon" aria-label="Ver muestras"',
            ], false)
            ->assertDontSee('site-hero__facts', false);
    }

    public function test_the_phone_header_shows_the_create_button_next_to_the_menu(): void
    {
        $start = DiyInvitations::startUrl(null);
        $html = $this->withoutVite()->get('/')->assertOk()->getContent();

        // Visible sin abrir el menú, antes del botón del menú
        $this->assertMatchesRegularExpression('#class="site-btn site-btn--compact">Crear gratis</a>\s*<button type="button"#', $html);
        $this->assertStringContainsString('href="'.$start.'" class="site-btn site-btn--compact"', $html);

        // El idioma y el tema viven dentro del menú, con área de toque completa
        $menu = Str::between($html, 'id="menu-movil"', '</header>');
        $this->assertStringContainsString('hreflang="en"', $menu);
        $this->assertStringContainsString('site-touch', $menu);
    }

    public function test_the_other_site_pages_share_the_phone_header(): void
    {
        foreach (['/precios', '/unete', '/invitaciones-de-boda', '/guia-invitaciones-digitales'] as $path) {
            $this->withoutVite()->get($path)->assertOk()->assertSee('site-btn--compact">Crear gratis', false);
        }

        $this->withoutVite()->get('/en')->assertOk()->assertSee('site-btn--compact">Start free', false)->assertSee('Create my invitation for free');
    }

    public function test_the_samples_button_is_not_offered_when_there_is_nothing_to_show(): void
    {
        $this->withoutVite()->get('/')
            ->assertOk()
            ->assertSee('Crear mi invitación gratis')
            ->assertDontSee('Ver muestras')
            ->assertDontSee('href="#plantillas"', false);
    }

    public function test_the_event_landings_describe_the_self_service_path(): void
    {
        foreach (['/invitaciones-de-boda', '/invitaciones-xv-anos', '/invitaciones-de-bautizo', '/invitaciones-de-cumpleanos'] as $path) {
            $this->withoutVite()->get($path)
                ->assertOk()
                ->assertSee('Crea tu cuenta y ármala')
                ->assertSee('Publícala y compártela')
                ->assertSee('¿Prefieres que la armemos por ti?')
                ->assertDontSee('Mándanos tus datos')
                ->assertDontSee('lo armamos con')
                ->assertDontSee('Armamos la invitación');
        }

        $this->withoutVite()->get('/invitaciones-de-boda')->assertSee('Elijan el diseño y ármenla con sus fotos y sus colores');
    }

    public function test_the_season_pages_keep_the_service_flow(): void
    {
        // Las de temporada se piden por WhatsApp y las armamos nosotros (mientras la temporada está en venta:
        // con su fecha vigente y sus muestras sembradas; terminada, ver SeasonEndedLandingTest)
        $seeder = new ShowcaseInvitationsSeeder;
        $seeder->seedSlug('tarjeta-ana-luis');
        $seeder->seedSlug('halloween-noche-diego');
        config(['bida.seasons.amor.ends_at' => now()->addWeek()->toDateTimeString(), 'bida.seasons.halloween.ends_at' => now()->addWeek()->toDateTimeString()]);

        $this->withoutVite()->get('/invitaciones-de-halloween')
            ->assertOk()
            ->assertSee('Mándanos tus datos')
            ->assertDontSee('¿Prefieres que la armemos por ti?');

        $this->withoutVite()->get('/tarjetas-dia-del-amor')
            ->assertOk()
            ->assertSee('Mándanos tu foto y tu mensaje')
            ->assertDontSee('¿Prefieres que la armemos por ti?');
    }

    public function test_the_english_landing_tells_the_same_story(): void
    {
        $this->withoutVite()->get('/en/wedding-invitations')
            ->assertOk()
            ->assertSee('Create your account and build it')
            ->assertSee('Publish it and share it')
            ->assertSee('Prefer that we build it for you?')
            ->assertDontSee('Send us your details');
    }
}
