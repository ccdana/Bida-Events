<?php

namespace Tests\Feature;

use App\Support\ShowcaseDemos;
use App\Support\TrendTemplates;
use Database\Seeders\ShowcaseInvitationsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Esqueletos de carga del sitio público: la foto y el teléfono de la portada, las capturas de las plantillas y la
 * foto de cada página por evento muestran un bloque con brillo hasta que llega lo suyo (resources/css/site/skeleton.css).
 */
class LoadingSkeletonsTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public function test_the_home_hero_photo_and_phone_start_as_skeletons_and_the_photo_fades_in_when_decoded(): void
    {
        (new ShowcaseInvitationsSeeder)->seedSlug('xv-isabella');

        $this->withoutVite()->get('/')
            ->assertOk()
            // La foto principal es un esqueleto desde el primer pintado; la pantalla del teléfono lo es siempre
            ->assertSee('class="site-stage site-hero__photo site-skel"', false)
            ->assertSee('class="site-phone__screen"', false)
            // «hero-ready» se pone al decodificar la foto, con un tope largo por si nunca llega
            ->assertSee("classList.add('hero-ready')", false)
            ->assertSee('photo.decode()', false)
            ->assertSee('setTimeout(go, 4500)', false)
            // El brillo del esqueleto sigue hasta que la foto llega de verdad, no solo hasta el tope de tiempo
            ->assertSee("classList.add('hero-photo-in')", false);
    }

    public function test_every_site_page_marks_slow_devices_so_the_skeletons_stay_still(): void
    {
        $this->withoutVite()->get('/')
            ->assertOk()
            ->assertSee("classList.add('site-lite')", false)
            // Ahorro de datos, poca memoria o pocos núcleos; «?hero=lite» y «?hero=live» lo fuerzan
            ->assertSee('saveData === true', false)
            ->assertSee('deviceMemory <= 2', false)
            ->assertSee('hardwareConcurrency <= 2', false)
            ->assertSee('hero=(lite|live)', false);

        $this->withoutVite()->get('/login')->assertOk()->assertSee("classList.add('site-lite')", false);
    }

    public function test_the_event_page_photo_and_captures_wait_inside_a_skeleton_and_say_when_they_arrive(): void
    {
        $invitation = $this->createInvitation(['slug' => 'muestra-esqueleto', 'template' => TrendTemplates::key('tendedero', 'babyshower')]);
        config(['bida.landings.invitaciones-de-baby-shower.demos' => [$invitation->slug]]);
        $capture = public_path(ShowcaseDemos::CAPTURES.'/muestra-esqueleto.webp');
        File::ensureDirectoryExists(dirname($capture));
        File::put($capture, 'webp');

        try {
            $html = $this->withoutVite()->get(route('landing', 'invitaciones-de-baby-shower'))->assertOk()->getContent();
        } finally {
            File::delete($capture);
        }

        // La foto del evento, detrás de los teléfonos, avisa al llegar (o al fallar) para apagar el brillo y fundirse
        $this->assertStringContainsString('class="site-landing__photo site-skel site-skel--fade"', $html);
        $this->assertMatchesRegularExpression('/<img [^>]*fetchpriority="high"[^>]*onload="this\.parentNode\.classList\.add\(\'is-live\'\)" onerror="this\.parentNode\.classList\.add\(\'is-live\'\)"/', $html);

        // La captura de cada plantilla, igual: su pantalla espera con brillo y la imagen se funde al llegar
        $this->assertStringContainsString('class="site-phone__screen site-skel--fade"', $html);
        $this->assertMatchesRegularExpression('/<img src="[^"]*muestra-esqueleto\.webp[^"]*"[^>]*onload="this\.parentNode\.classList\.add\(\'is-live\'\)" onerror="this\.parentNode\.classList\.add\(\'is-live\'\)"/', $html);
    }

    public function test_a_design_without_a_capture_also_stops_its_skeleton_when_the_live_opening_loads(): void
    {
        $invitation = $this->createInvitation(['slug' => 'muestra-sin-captura', 'template' => TrendTemplates::key('tendedero', 'babyshower')]);
        config(['bida.landings.invitaciones-de-baby-shower.demos' => [$invitation->slug]]);

        $this->withoutVite()->get(route('landing', 'invitaciones-de-baby-shower'))
            ->assertOk()
            ->assertSee('class="site-phone__screen site-skel--fade"', false)
            ->assertSee('<iframe src="'.route('invitation.demo', 'muestra-sin-captura').'"', false)
            ->assertSee("onload=\"this.parentNode.classList.add('is-live')\"", false);
    }

    public function test_the_skeleton_styles_are_quiet_on_slow_devices_and_never_hide_a_photo_without_javascript(): void
    {
        $css = file_get_contents(resource_path('css/site/skeleton.css'));

        // El brillo solo se mueve con transform y se apaga al llegar lo esperado
        $this->assertStringContainsString('@keyframes site-skel-sweep', $css);
        $this->assertStringContainsString('transform: translateX(100%)', $css);
        $this->assertStringContainsString('.site-skel.is-live::before', $css);
        $this->assertStringNotContainsString('background-position', $css);

        // Equipos lentos y movimiento reducido: sin animación
        $this->assertMatchesRegularExpression('/\.site-lite \.site-skel::before,\s*\.site-lite \.site-phone__screen::before\s*\{\s*animation: none;/', $css);
        $this->assertMatchesRegularExpression('/@media \(prefers-reduced-motion: reduce\)\s*\{\s*\.site-skel::before,\s*\.site-phone__screen::before\s*\{\s*animation: none;/', $css);

        // Sin JavaScript no se oculta ninguna imagen ni se pinta brillo encima de una foto
        $this->assertStringContainsString('.js:not(.site-reveal-all) .site-skel--fade:not(.is-live) > :is(img, iframe)', $css);
        $this->assertStringContainsString('.js .site-skel::before', $css);

        // Lo carga solo el CSS del sitio público, y el viejo brillo de site.css ya no existe
        $this->assertStringContainsString("@import './site/skeleton.css';", file_get_contents(resource_path('css/public.css')));
        $this->assertStringNotContainsString('site-screen-wait', file_get_contents(resource_path('css/site/site.css')));
    }

    public function test_the_hero_no_longer_hides_its_photo_and_phone_behind_an_entrance_animation(): void
    {
        $home = file_get_contents(resource_path('css/site/home.css'));

        $this->assertMatchesRegularExpression('/\.site-hero__photo\s*\{[^}]*animation: none;/', $home);
        $this->assertMatchesRegularExpression('/\.site-hero__phone\s*\{[^}]*animation: none;/', $home);
        $this->assertStringContainsString('.js:not(.hero-ready):not(.site-reveal-all) .site-hero__photo > img.is-active', $home);
        $this->assertStringContainsString(':is(.hero-photo-in, .site-reveal-all) .site-hero__photo::before', $home);
        $this->assertStringNotContainsString('animation-play-state: paused', file_get_contents(resource_path('css/site/site.css')));
    }

    public function test_what_alpine_toggles_stays_hidden_until_alpine_loads_on_the_public_site(): void
    {
        // Sin esta regla el menú del celular se ve abierto mientras carga el script (estaba solo en app.css)
        $this->assertStringContainsString('[x-cloak] { display: none !important; }', file_get_contents(resource_path('css/public.css')));
        $this->assertStringContainsString('<div id="menu-movil" x-show="open" x-cloak', $this->withoutVite()->get('/')->getContent());
    }

    public function test_the_invitation_inside_the_home_phone_does_not_start_alpine_below_the_cover(): void
    {
        $app = file_get_contents(resource_path('js/app.js'));

        // Solo con ?portada=1 y en una invitación: el contenido y el pie quedan fuera de Alpine; las invitaciones reales, completas
        $this->assertStringContainsString("document.body.classList.contains('inv-page') && new URLSearchParams(window.location.search).has('portada')", $app);
        $this->assertStringContainsString("querySelectorAll('#contenido, .inv-footer')", $app);
        $this->assertStringContainsString("setAttribute('x-ignore', '')", $app);
        // Las dos formas de arrancar Alpine pasan por ahí
        $this->assertSame(2, substr_count($app, 'startAlpine();'));
        $this->assertSame(1, substr_count($app, 'Alpine.start();'));
    }

    public function test_the_site_script_marks_the_page_as_lite_when_it_measures_a_slow_screen(): void
    {
        $this->assertStringContainsString("root.classList.add('site-lite')", file_get_contents(resource_path('js/site.js')));
    }
}
