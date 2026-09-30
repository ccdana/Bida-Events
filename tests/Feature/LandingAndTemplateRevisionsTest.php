<?php

namespace Tests\Feature;

use App\Http\Controllers\HomeController;
use App\Services\InvitationModuleService;
use App\Support\InvitationDefaults;
use App\Support\InvitationTemplates;
use App\Support\ShowcaseDemos;
use App\Support\TrendTemplates;
use Database\Seeders\ShowcaseInvitationsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Páginas por evento con los diseños como capturas de su apertura, el baby shower en la portada,
 * «La gota» rehecha con la apertura de la pila, y las aperturas y fondos nuevos de las temáticas.
 */
class LandingAndTemplateRevisionsTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public function test_each_event_page_shows_its_designs_as_captures_that_open_the_full_demo(): void
    {
        $this->seed(ShowcaseInvitationsSeeder::class);

        $this->withoutVite()->get(route('landing', 'invitaciones-de-graduacion'))
            ->assertOk()
            ->assertSee('id="disenos"', false)
            // Cada diseño lleva a su muestra completa, en el orden de la página
            ->assertSeeInOrder([
                'href="'.route('invitation.demo', 'graduacion-mariana').'"',
                'href="'.route('invitation.demo', 'graduacion-mariana-salidas').'"',
                'href="'.route('invitation.demo', 'graduacion-mariana-revista').'"',
            ], false)
            ->assertSeeInOrder(['Birrete al aire', 'Próxima salida', 'Edición especial'])
            ->assertSee('Verla completa')
            // Ya no está la sección de tarjetas ni el teléfono interactivo con pestañas
            ->assertDontSee('la misma invitación completa')
            ->assertDontSee('Probar en el teléfono')
            ->assertDontSee('role="tablist"', false);
    }

    public function test_a_design_with_a_capture_shows_the_image_instead_of_the_live_demo(): void
    {
        $invitation = $this->createInvitation(['slug' => 'muestra-captura', 'template' => TrendTemplates::key('tendedero', 'babyshower')]);
        config(['bida.landings.invitaciones-de-baby-shower.demos' => [$invitation->slug]]);
        $capture = public_path(ShowcaseDemos::CAPTURES.'/muestra-captura.webp');
        File::ensureDirectoryExists(dirname($capture));
        File::put($capture, 'webp');

        try {
            $this->withoutVite()->get(route('landing', 'invitaciones-de-baby-shower'))
                ->assertOk()
                ->assertSee(asset(ShowcaseDemos::CAPTURES.'/muestra-captura.webp').'?v=', false)
                ->assertSee('href="'.route('invitation.demo', 'muestra-captura').'"', false)
                ->assertDontSee('<iframe', false);
        } finally {
            File::delete($capture);
        }

        // Sin captura todavía, la apertura se ve en vivo (quieta, sin recibir toques)
        $this->withoutVite()->get(route('landing', 'invitaciones-de-baby-shower'))
            ->assertOk()
            ->assertSee('<iframe src="'.route('invitation.demo', 'muestra-captura').'"', false);
    }

    public function test_the_home_lets_visitors_try_the_baby_shower_without_adding_it_to_the_hero_phone(): void
    {
        $this->seed(ShowcaseInvitationsSeeder::class);

        $html = $this->withoutVite()->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder(['id="plantillas"', 'Baby shower', 'Tendedero'], false)
            ->assertSee('href="'.route('invitation.demo', 'babyshower-valentina').'"', false)
            ->getContent();

        // El teléfono de la portada solo recorre los eventos que tienen foto y palabra arriba
        $this->assertMatchesRegularExpression('/data-cover-reel="([^"]*)"/', $html);
        preg_match('/data-cover-reel="([^"]*)"/', $html, $reel);
        $this->assertStringContainsString('xv-isabella', $reel[1]);
        $this->assertStringNotContainsString('babyshower-valentina', $reel[1]);
    }

    public function test_la_gota_opens_pouring_the_water_and_shows_the_photo_under_the_water(): void
    {
        $data = ShowcaseInvitationsSeeder::data('bautizo-emilia-gota');
        $data['modules']['config']['template'] = InvitationTemplates::BAUTIZO_LA_GOTA;
        $invitation = $this->createInvitation(['template' => InvitationTemplates::BAUTIZO_LA_GOTA]);
        app(InvitationModuleService::class)->syncAllModules($invitation, $data['modules']);

        $this->withoutVite()->get(route('invitation.show', $invitation->slug))
            ->assertOk()
            ->assertSee('inv-page inv-gota inv-themed inv-trend', false)
            // La apertura de la pila con la jarra
            ->assertSee('gt-font__ewer', false)
            ->assertSee('Toca la jarra para verter el agua')
            // La portada: la gota cae del aire al agua y la foto queda en el centro de las ondas
            ->assertSeeInOrder(['gt-hero__air', 'gt-hero__fall', 'gt-hero__water', 'gt-pool__ring', 'gt-pool__photo'], false)
            ->assertSee('gt-when', false)
            // Los padrinos, uno tras otro como las ondas del agua
            ->assertSeeInOrder(['gt-ripple', 'Padrinos de bautizo', 'Natalia Peña y Javier Soliz', 'Padrinos de vela'], false)
            ->assertSee('gt-family', false);
    }

    public function test_an_event_page_with_many_designs_shows_them_in_a_row_below_the_photo(): void
    {
        $this->seed(ShowcaseInvitationsSeeder::class);

        $this->withoutVite()->get(route('landing', 'invitaciones-de-bautizo'))
            ->assertOk()
            ->assertSee('id="disenos"', false)
            ->assertSee('site-shots site-shots--row', false)
            ->assertDontSee('site-shots--fan', false)
            ->assertSee('Mapa de estrellas')
            ->assertSee('Bordado a mano')
            ->assertSee('href="'.route('invitation.demo', 'bautizo-emilia-estrellas').'"', false)
            ->assertSee('href="'.route('invitation.demo', 'bautizo-emilia-bordado').'"', false);
    }

    public function test_the_home_previews_every_design_of_each_event_like_its_page(): void
    {
        // Fuera de temporada, Halloween también se prueba en la portada
        config(['bida.seasons.halloween.ends_at' => '2026-10-31 23:59:59']);
        $this->travelTo(Carbon::parse('2026-11-05 12:00:00', 'America/La_Paz'));
        $this->seed(ShowcaseInvitationsSeeder::class);

        $html = $this->withoutVite()->get(route('home'))
            ->assertOk()
            // La portada ya no tiene los botones de «Probar una invitación» ni «Escríbenos» debajo del texto
            ->assertDontSee('Probar una invitación')
            ->assertDontSee('site-hero__actions', false)
            // Una pestaña por evento (un índice tipográfico, sin íconos) y su fila de capturas, con el enlace a su página
            ->assertSee('role="tablist"', false)
            ->assertSee('site-events__tab', false)
            ->assertDontSee('site-chip', false)
            ->assertSeeInOrder(['id="plantillas"', 'Bordado a mano', 'Caldero encantado'], false)
            ->assertSee('href="'.route('invitation.demo', 'halloween-noche-diego-caldero').'"', false)
            ->assertSee('href="'.route('landing', 'invitaciones-de-halloween').'"', false)
            ->getContent();

        $this->assertSame(count(HomeController::designsByEvent(config('bida'))), substr_count($html, 'class="site-tester__panel"'));
        $this->assertStringNotContainsString('Abrir en pantalla completa', $html);
    }

    public function test_during_its_season_halloween_is_tried_from_the_season_panel_with_every_design(): void
    {
        config(['bida.seasons.halloween.ends_at' => '2026-10-31 23:59:59']);
        $this->travelTo(Carbon::parse('2026-10-20 12:00:00', 'America/La_Paz'));
        $this->seed(ShowcaseInvitationsSeeder::class);

        $html = $this->withoutVite()->get(route('home'))->assertOk()->getContent();
        $templates = Str::betweenFirst($html, 'id="plantillas"', '</section>');
        $season = Str::betweenFirst($html, 'id="temporada-halloween"', '</section>');

        $this->assertStringNotContainsString('Caldero encantado', $templates);
        $this->assertStringContainsString('Caldero encantado', $season);
        $this->assertStringContainsString('Función de medianoche', $season);
    }

    /** «Carta de baile» y «Galería Quince» se reemplazaron: sus invitaciones pasan al diseño nuevo sin perder nada. */
    public function test_invitations_with_the_replaced_xv_templates_move_to_the_new_designs(): void
    {
        $carta = $this->createInvitation(['template' => 'invitations.templates.xv-carta-de-baile']);
        $galeria = $this->createInvitation(['template' => 'invitations.templates.xv-galeria']);

        (require database_path('migrations/2026_09_29_000001_replace_xv_templates.php'))->up();

        $this->assertSame(TrendTemplates::key('cuento', 'xv'), $carta->fresh()->template);
        $this->assertSame(TrendTemplates::key('caleidoscopio', 'xv'), $galeria->fresh()->template);

        // Un nombre viejo que llegue por otro lado (una sesión, un enlace del editor) también se resuelve
        $this->assertSame(TrendTemplates::key('cuento', 'xv'), InvitationDefaults::resolveTemplate('invitations.templates.xv-carta-de-baile'));
        $this->assertSame(TrendTemplates::key('caleidoscopio', 'xv'), InvitationDefaults::resolveTemplate('invitations.templates.xv-galeria'));

        $this->withoutVite()->get(route('invitation.show', $carta->slug))->assertOk()->assertSee('inv-page inv-cuento', false);
    }

    /** Aperturas mejoradas y fondos nuevos: lo que distingue a cada una en el HTML. */
    public static function revisions(): array
    {
        return [
            'Tendedero' => [TrendTemplates::key('tendedero', 'babyshower'), 'babyshower-valentina', ['td-basket__peek--onesie', 'td-yard__spare', 'td-ambient__line', 'td-ambient__bubble']],
            'Edición especial' => [TrendTemplates::key('revista', 'graduacion'), 'graduacion-mariana', ['rv-awning', 'rv-rack__issue', 'rv-issue__inside', 'rv-ambient__cut']],
            'Gira mundial' => [TrendTemplates::key('gira', 'cumple'), 'cumple-daniela-30', ['gr-rig__light', 'gr-rig__screen', 'gr-crowd', 'gr-intro__confetti']],
            'Partitura a dos voces' => [TrendTemplates::key('partitura', 'boda'), 'boda-camila-andres', ['pt-ambient__staff', 'pt-ambient__note']],
            'Próxima salida' => [InvitationTemplates::GRADUACION_PROXIMA_SALIDA, 'graduacion-mariana', ['ps-gate__plane', 'ps-gate__status-next', 'ps-pass__scan', 'Embarcando hoy']],
            'Álbum de stickers' => [InvitationTemplates::CUMPLE_STICKERS, 'cumple-daniela-stickers', ['st-album__slot', 'st-fan st-fan--shiny', 'st-album__done', 'st-pack__label']],
            'Dos caminos' => [InvitationTemplates::BODA_DOS_CAMINOS, 'boda-camila-andres-caminos', ['dc-map__compass', 'dc-map__walk', 'animateMotion', 'dc-walk__heart']],
            'Móvil de cuna: el carrusel' => [TrendTemplates::key('movil', 'bautizo'), 'bautizo-emilia', ['mv-intro__projector', 'mv-intro__notes', 'mv-intro__light', 'mv-intro__window']],
            'Partitura a dos voces: la batuta' => [TrendTemplates::key('partitura', 'boda'), 'boda-camila-andres', ['pt-baton', 'pt-score__cover', 'pt-score__note--2', 'pt-score__heart']],
            // Las cinco de XV, rehechas: la funda del Atelier, el libro desplegable, el visor del caleidoscopio,
            // la llave del joyero y la caja del perfume, cada una con su fondo propio
            'Atelier' => [InvitationTemplates::XV_PREMIUM, 'xv-isabella', ['at-zip__pull', 'at-sketch__gown', 'at-sheet__fields', 'at-ambient__tape--right', 'Toca el cierre para abrir la funda']],
            'Cuento desplegable' => [TrendTemplates::key('cuento', 'xv'), 'xv-isabella', ['cu-popup__name', 'cu-cover__back', 'cu-message', 'cu-ambient__edge--left']],
            'Caleidoscopio' => [TrendTemplates::key('caleidoscopio', 'xv'), 'xv-isabella', ['ka-scope__ring', 'ka-scope__center', 'ka-facet--wide', 'ka-ambient__mandala']],
            'Joyero musical' => [TrendTemplates::key('joyero', 'xv'), 'xv-isabella', ['jo-box__lining', 'jo-figure__svg', 'jo-case__tray', 'jo-ambient__quilt']],
            // Las dos de boda nuevas: la servilleta que se aparta del plato y los dos relojes que se vuelven uno
            'Mesa de honor' => [TrendTemplates::key('mesa', 'boda'), 'boda-camila-andres', ['ms-plate__monogram', 'ms-placecard__guest', 'ms-course__number', 'ms-ambient__glow--right']],
            'A la misma hora' => [TrendTemplates::key('reloj', 'boda'), 'boda-camila-andres', ['rl-hand--second', 'rl-watch__crown', 'rl-intro__together', 'rl-ambient__gear--right-small']],
            // Las dos de cumpleaños nuevas: las hojas del almanaque que se arrancan y la cabina de fotos con su tira
            'Día feriado' => [TrendTemplates::key('feriado', 'cumple'), 'cumple-daniela-30', ['fd-leaf--day', 'fd-sticky', 'fd-month__day is-marked', 'fd-ambient__leaf--right']],
            'Cabina de fotos' => [TrendTemplates::key('cabina', 'cumple'), 'cumple-daniela-30', ['cb-booth__screen', 'cb-strip--intro', 'cb-intro__guest', 'cb-ambient__flash--right']],
            'Esencia XV' => [TrendTemplates::key('esencia', 'xv'), 'xv-isabella', ['ez-mist', 'ez-intro__reveal', 'ez-tier--2', 'ez-ambient__mist--center']],
        ];
    }

    #[DataProvider('revisions')]
    public function test_each_revised_template_has_its_new_opening_and_background(string $template, string $demo, array $markers): void
    {
        $data = ShowcaseInvitationsSeeder::data($demo);
        $data['modules']['config']['template'] = $template;
        $invitation = $this->createInvitation(['template' => $template]);
        app(InvitationModuleService::class)->syncAllModules($invitation, $data['modules']);
        $guest = $invitation->guests()->create(['name' => 'Familia Quispe', 'passes_allocated' => 2]);

        $html = $this->withoutVite()
            ->get(route('invitation.guest', ['slug' => $invitation->slug, 'token' => $guest->qr_code_token]))
            ->assertOk()
            ->getContent();

        foreach ($markers as $marker) {
            $this->assertStringContainsString($marker, $html, "«{$template}» no muestra «{$marker}»");
        }
    }
}
