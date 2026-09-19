<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\SiteImage;
use Database\Seeders\ShowcaseInvitationsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_see_the_packages_and_whatsapp_contact(): void
    {
        config(['bida.whatsapp' => '+591 7123-4567', 'bida.facebook' => 'bidaeventsbo']);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('Invitaciones digitales para bodas, bautizos, cumpleaños y XV años')
            // Promoción de inauguración: el precio normal tachado y el de hoy
            ->assertSeeInOrder(['200 Bs', '150', 'Bs', '400 Bs', '300', 'Bs', '700 Bs', '500', 'Bs'])
            ->assertSee('Promoción de inauguración')
            ->assertSee('Ahorras 50 Bs')
            // Servicios: las invitaciones y las tarjetas, cada una con su precio de hoy
            ->assertSee('id="servicios"', false)
            ->assertSeeInOrder(['Invitaciones digitales', 'desde', '150 Bs', 'Tarjetas digitales'])
            // Los recuerdos en vivo van dentro de lo que incluye la invitación, y ya no hay sección de contacto
            ->assertSeeInOrder(['id="incluye"', 'Recuerdos en vivo', 'id="precios"'], false)
            ->assertDontSee('¿Ya tienes fecha')
            ->assertSee('https://wa.me/59171234567?text=', false)
            ->assertSee(rawurlencode('me interesa el paquete Estándar (300 Bs)'), false)
            ->assertSee(route('login'), false)
            ->assertSee('Ingresar')
            ->assertSee('https://www.facebook.com/bidaeventsbo', false);
    }

    public function test_without_the_launch_promo_the_packages_show_their_normal_price(): void
    {
        config(['bida.launch_promo.active' => false]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Promoción de inauguración')
            ->assertDontSee('<del class="site-plan__old">', false)
            ->assertSee(rawurlencode('me interesa el paquete Estándar (400 Bs)'), false);
    }

    public function test_the_season_opens_the_home_with_its_countdown_and_promo_price(): void
    {
        config(['bida.season.ends_at' => '2026-09-21 23:59:59', 'bida.whatsapp' => '+591 7123-4567']);
        $this->travelTo(Carbon::parse('2026-09-19 12:00:00', 'America/La_Paz'));
        $this->seed(ShowcaseInvitationsSeeder::class);

        $response = $this->withoutVite()->get(route('home'))->assertOk();

        $response
            // Va antes que la portada, como gancho
            ->assertSeeInOrder(['id="temporada"', 'Invitaciones digitales'], false)
            // Habla de la temporada y lista sus diseños (hoy, uno)
            ->assertSee('Día del Amor y la Primavera')
            ->assertSeeInOrder(['Diseños de la temporada', 'Carta de amor'])
            ->assertSeeInOrder(['100 Bs', '75', 'Bs'])
            // Cuenta regresiva ya calculada: faltan 2 días, 11 horas, 59 minutos y 59 segundos
            ->assertSeeInOrder(['02', 'días', '11', 'horas', '59', 'min', '59', 'seg'])
            ->assertSee('seasonOffer(', false)
            // WhatsApp con el precio y el código de la campaña
            ->assertSee(rawurlencode('quiero una tarjeta del Día del Amor (75 Bs)'), false)
            ->assertSee(rawurlencode('Ref. AMOR'), false)
            // El teléfono abre la tarjeta sola
            ->assertSee(route('invitation.demo', ['slug' => 'tarjeta-ana-luis', 'portada' => 1]), false)
            ->assertSee('href="#temporada"', false);

        // La tarjeta no se mezcla con las plantillas de invitación
        $this->assertStringNotContainsString(
            'plantilla-tab-4',
            $response->getContent(),
        );
    }

    public function test_the_season_disappears_when_its_date_passes(): void
    {
        config(['bida.season.ends_at' => '2026-09-21 23:59:59']);
        $this->travelTo(Carbon::parse('2026-09-22 00:00:01', 'America/La_Paz'));
        $this->seed(ShowcaseInvitationsSeeder::class);

        $this->withoutVite()
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('id="temporada"', false)
            ->assertDontSee('href="#temporada"', false);
    }

    public function test_home_lists_every_configured_event_type_and_the_brand_logo(): void
    {
        config(['bida.event_types' => ['Bodas' => 'heart', 'Aniversarios' => 'champagne']]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Bodas')
            ->assertSee('Aniversarios')
            ->assertSee('brand-mark', false)
            ->assertSee('favicon.svg', false)
            ->assertSee('onclick="toggleTheme()"', false);
    }

    public function test_the_demo_preview_falls_back_to_an_image_when_the_demo_is_missing(): void
    {
        config(['bida.demo_slug' => 'no-existe']);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('<iframe', false);
    }

    public function test_visitors_can_pick_a_showcase_invitation_for_each_template(): void
    {
        $this->seed(ShowcaseInvitationsSeeder::class);

        $this->withoutVite()
            ->get(route('home'))
            ->assertOk()
            ->assertSee('id="plantillas"', false)
            ->assertSee('href="#plantillas"', false)
            ->assertSeeInOrder(['XV Años Elegante', 'Boda Jardín', 'Bautizo Cielo', 'Cumpleaños Fiesta'])
            // El teléfono de la sección prueba la muestra interactiva; el de la portada recorre las aperturas
            ->assertSee('src="'.route('invitation.demo', 'xv-isabella').'"', false)
            ->assertSee('data-cover-reel', false)
            ->assertSee(route('invitation.demo', ['slug' => 'boda-camila-andres', 'portada' => 1]), false)
            // "Abrir en pantalla completa" lleva a la muestra, no a la invitación real
            ->assertSee('href="'.route('invitation.demo', 'cumple-daniela-30').'"', false)
            ->assertDontSee('href="'.route('invitation.show', 'cumple-daniela-30').'"', false)
            // La foto y la palabra de la portada siguen el orden del teléfono y cambian con él
            ->assertSee('data-rotator-driven', false)
            ->assertSeeInOrder(['tus XV años', 'tu boda', 'tu bautizo', 'tu cumpleaños']);
    }

    public function test_the_templates_section_is_hidden_without_showcase_invitations(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('id="plantillas"', false)
            ->assertDontSee('href="#plantillas"', false);
    }

    public function test_signed_in_users_get_a_link_to_their_panel(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Mi panel')
            ->assertSee(route('dashboard'), false);
    }

    public function test_login_page_uses_the_site_layout_without_demo_credentials_outside_local(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Ingresa a tu cuenta')
            ->assertSee('name="username"', false)
            ->assertSee('name="password"', false)
            ->assertSee('data-rotator', false)
            ->assertDontSee('admin@test.com');
    }

    public function test_site_images_use_the_local_file_when_it_exists(): void
    {
        $path = 'images/site/_test-image.webp';
        config(['bida.images._test-image' => ['path' => $path, 'size' => [300, 200]]]);

        $this->assertSame('https://picsum.photos/seed/bida-_test-image/300/200?grayscale', SiteImage::url('_test-image'));

        File::ensureDirectoryExists(dirname(public_path($path)));
        File::put(public_path($path), 'webp');

        try {
            $this->assertSame(asset($path), SiteImage::url('_test-image'));
        } finally {
            File::delete(public_path($path));
        }
    }
}
