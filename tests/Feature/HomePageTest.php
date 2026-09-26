<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\SiteImage;
use Database\Seeders\ShowcaseInvitationsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_see_the_packages_and_whatsapp_contact(): void
    {
        config(['bida.whatsapp' => '+591 7123-4567', 'bida.facebook' => 'bidaeventsbo']);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('Invitaciones digitales para bodas, XV años, bautizos, cumpleaños y graduaciones')
            // Promoción de inauguración: el precio normal tachado y el de hoy
            ->assertSeeInOrder(['US$ 29', '22', 'USD', 'US$ 57', '43', 'USD', 'US$ 99', '72', 'USD'])
            // Solo con los precios: sin texto de la promoción sobre los paquetes
            ->assertDontSee('Promoción de inauguración')
            ->assertSee('Ahorras US$ 7')
            // Servicios: las invitaciones, lo de temporada y «Hazlo tú», cada uno con su precio de hoy (en dólares)
            ->assertSee('id="servicios"', false)
            ->assertSeeInOrder(['Invitaciones digitales', 'Desde', 'US$ 22', 'Diseños de temporada', 'Hazlo tú', 'Desde', 'US$ 9', 'al mes'])
            ->assertSee(route('diy'), false)
            // Los recuerdos en vivo van dentro de lo que incluye la invitación, y ya no hay sección de contacto
            ->assertSeeInOrder(['id="incluye"', 'Pase QR y control de entrada', 'Fotomural en vivo', 'id="precios"'], false)
            ->assertDontSee('¿Ya tienes fecha')
            ->assertSee('https://wa.me/59171234567?text=', false)
            ->assertSee(rawurlencode('me interesa el paquete Estándar (US$ 43)'), false)
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
            ->assertSee(rawurlencode('me interesa el paquete Estándar (US$ 57)'), false);
    }

    public function test_the_season_waits_behind_a_floating_button_with_its_countdown_and_promo_price(): void
    {
        config(['bida.seasons.halloween.ends_at' => '2026-10-31 23:59:59', 'bida.whatsapp' => '+591 7123-4567']);
        $this->travelTo(Carbon::parse('2026-10-28 12:00:00', 'America/La_Paz'));
        $this->seed(ShowcaseInvitationsSeeder::class);

        $response = $this->withoutVite()->get(route('home'))->assertOk();

        $response
            // Ya no va arriba de la portada: un botón que sigue al scroll abre el panel
            ->assertSeeInOrder(['Invitaciones digitales', 'site-seasons__fab', 'id="temporada-halloween"'], false)
            ->assertSeeInOrder(['De temporada', 'Halloween', '3', 'días'])
            // El panel habla de la temporada y lista sus diseños (hoy, uno)
            ->assertSee('Tu fiesta de Halloween empieza en la invitación')
            ->assertSeeInOrder(['Diseños de la temporada', 'Noche de calabazas'])
            ->assertSeeInOrder(['US$ 26', '20', 'USD'])
            // Cuenta regresiva ya calculada: faltan 3 días, 11 horas, 59 minutos y 59 segundos
            ->assertSeeInOrder(['03', 'días', '11', 'horas', '59', 'min', '59', 'seg'])
            ->assertSee('seasonOffer(', false)
            // WhatsApp con el precio y el código de la campaña
            ->assertSee(rawurlencode('quiero una invitación para mi fiesta de Halloween (US$ 20)'), false)
            ->assertSee(rawurlencode('Ref. HALLO'), false)
            // El teléfono carga la muestra recién al abrir el panel
            ->assertSee('data-lazy-src="'.route('invitation.demo', ['slug' => 'halloween-noche-diego', 'portada' => 1]).'"', false)
            ->assertDontSee(' src="'.route('invitation.demo', ['slug' => 'halloween-noche-diego', 'portada' => 1]).'"', false)
            // Los enlaces a «#temporada» (servicios) abren el mismo panel
            ->assertSee('href="#temporada"', false);

        // La temporada no se mezcla con las plantillas de invitación
        $templates = Str::betweenFirst($response->getContent(), 'id="plantillas"', '</section>');
        $this->assertStringContainsString('Sopla las velas', $templates);
        $this->assertStringNotContainsString('Noche de calabazas', $templates);
    }

    public function test_the_season_disappears_when_its_date_passes(): void
    {
        config(['bida.seasons.halloween.ends_at' => '2026-10-31 23:59:59']);
        $this->travelTo(Carbon::parse('2026-11-01 00:00:01', 'America/La_Paz'));
        $this->seed(ShowcaseInvitationsSeeder::class);

        $this->withoutVite()
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('site-seasons__fab', false)
            ->assertDontSee('id="temporada-halloween"', false)
            ->assertDontSee('href="#temporada"', false);
    }

    public function test_each_season_has_its_own_button_and_can_be_switched_off_alone(): void
    {
        config([
            'bida.seasons.amor.ends_at' => '2026-10-31 23:59:59',
            'bida.seasons.halloween.ends_at' => '2026-10-31 23:59:59',
        ]);
        $this->travelTo(Carbon::parse('2026-10-20 12:00:00', 'America/La_Paz'));
        $this->seed(ShowcaseInvitationsSeeder::class);

        // Las dos a la vez: un solo botón que las agrupa, y se elige cuál ver dentro de la hoja
        $this->withoutVite()->get(route('home'))
            ->assertOk()
            ->assertSee('id="temporada-amor"', false)
            ->assertSee('id="temporada-halloween"', false)
            ->assertSee('2 temporadas')
            ->assertSee('site-seasons__tab', false)
            ->assertSee('Ahora: Día del Amor y la Primavera y Halloween');

        // Apagar la del Día del Amor no toca a Halloween
        config(['bida.seasons.amor.active' => false]);

        $this->withoutVite()->get(route('home'))
            ->assertOk()
            ->assertDontSee('id="temporada-amor"', false)
            ->assertSee('id="temporada-halloween"', false);
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
            ->assertSeeInOrder(['Noche de gala', 'Promesa en el jardín', 'Entre nubes', 'Sopla las velas'])
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
            // Con la versión por fecha del archivo, para que un cambio de foto no quede en la caché
            $this->assertSame(asset($path).'?v='.filemtime(public_path($path)), SiteImage::url('_test-image'));
        } finally {
            File::delete(public_path($path));
        }
    }
}
