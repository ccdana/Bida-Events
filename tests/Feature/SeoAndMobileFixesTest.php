<?php

namespace Tests\Feature;

use App\Support\InvitationTemplates;
use App\Support\ShowcaseDemos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Privacidad al día, ingreso sin esperas, banner de cookies corto, canónica y accesibilidad del sitio, insignia
 * «Nuevo» con sentido, precios y portada compactos en el celular y las invitaciones más livianas y legibles.
 */
class SeoAndMobileFixesTest extends TestCase
{
    use RefreshDatabase;

    // ── M5 · Privacidad y cookies

    public function test_the_privacy_policy_names_what_the_site_really_does(): void
    {
        $this->withoutVite()->get('/legal/privacidad')->assertOk()
            ->assertSee('nombre, correo electrónico, usuario y contraseña')
            ->assertSee('Si entras con Google')
            ->assertSee('Nunca recibimos tu contraseña de Google')
            ->assertSee('De los pagos: el plan o nivel')
            ->assertSee('nosotros no los vemos ni los guardamos')
            ->assertSee('Lemon Squeezy')
            ->assertSee('Libélula')
            ->assertSee('Zoho Mail')
            ->assertSee('YouTube')
            ->assertSee('una cookie que no es necesaria para usar el sitio y que no te identifica')
            ->assertSee('Enviarte los correos de tu cuenta')
            ->assertSee('Actualizada el 23 de octubre de 2026');
    }

    public function test_the_cookie_policy_calls_the_campaign_cookie_what_it_is(): void
    {
        $this->withoutVite()->get('/legal/cookies')->assertOk()
            ->assertSee('Cookie de campaña (no necesaria)')
            ->assertSee('No es necesaria para usar el sitio')
            ->assertSee('videos de YouTube')
            ->assertSee('pagar en las páginas de Lemon Squeezy o Libélula');
    }

    // ── M8 · Aviso de cookies compacto

    public function test_the_cookie_notice_is_short_and_honest(): void
    {
        $html = $this->withoutVite()->get('/')->assertOk()
            ->assertSee('Usamos las cookies necesarias y una de campaña que no te identifica. Nada de publicidad.')
            ->assertSee('Más información')
            ->assertDontSee('Usamos solo las cookies necesarias')
            ->getContent();

        $notice = Str::between($html, 'data-cookie-notice', '</div>');
        $this->assertLessThan(260, mb_strlen(trim(strip_tags($notice))), 'el aviso debe ser breve');

        // Una barra de una fila, no una tarjeta que se parte en varias
        $css = file_get_contents(resource_path('css/site/site.css'));
        $block = Str::before(Str::after($css, "\n.site-cookies {"), "\n}");
        $this->assertStringNotContainsString('flex-wrap', $block);
    }

    // ── M6 · Ingreso

    public function test_the_auth_forms_show_at_once_and_focus_the_first_error(): void
    {
        $css = file_get_contents(resource_path('css/site/site.css'));
        $this->assertMatchesRegularExpression('/\.site-auth form \.site-enter\s*\{\s*animation:\s*none;/', $css);

        foreach (['/login', '/registro', '/contrasena/olvide'] as $path) {
            $this->withoutVite()->get($path)->assertOk()
                ->assertSee('site-auth', false)
                ->assertSee('.site-auth [aria-invalid="true"]', false);
        }

        // Con un error, el campo lo declara (aria-invalid + aria-describedby) y el script lo enfoca
        $this->withoutVite()->from('/login')->followingRedirects()->post('/login', ['username' => 'nadie', 'password' => 'incorrecta'])
            ->assertSee('aria-invalid="true" aria-describedby="username-error"', false)
            ->assertSee('.site-auth [aria-invalid="true"]', false);
    }

    // ── M9 · «Nuevo» por fecha

    public function test_the_new_badge_lasts_thirty_days_from_the_launch(): void
    {
        $template = 'invitations.templates.xv-cuento';

        $this->travelTo('2026-10-23');
        $this->assertTrue(ShowcaseDemos::isNew($template));

        $this->travelTo('2026-10-28');
        $this->assertTrue(ShowcaseDemos::isNew($template), 'lanzada el 29 de septiembre: sigue hasta el 29 de octubre');

        $this->travelTo('2026-10-29');
        $this->assertFalse(ShowcaseDemos::isNew($template));

        $this->travelTo('2026-12-01');
        $this->assertFalse(ShowcaseDemos::isNew($template));
        // Las clásicas nunca la llevan
        $this->assertFalse(ShowcaseDemos::isNew('invitations.templates.xv-premium'));
    }

    public function test_every_new_design_has_a_launch_date(): void
    {
        $missing = collect(InvitationTemplates::all())
            ->filter(fn (array $template) => in_array($template['collection'] ?? null, ['nueva', 'tendencias'], true))
            ->keys()
            ->map(fn (string $key) => Str::afterLast($key, '.'))
            ->reject(fn (string $key) => config("bida.template_launched.{$key}") !== null)
            ->values()
            ->all();

        $this->assertSame([], $missing, 'Agrega su fecha en config/bida.php («template_launched»)');
    }

    // ── M10 · Precios y portada compactos

    public function test_the_price_cards_stack_on_phones_instead_of_sliding_sideways(): void
    {
        // Un carrusel horizontal alto le quita al celular el deslizar hacia abajo: las tarjetas van una bajo otra
        foreach (['/precios', '/unete', '/'] as $url) {
            $html = $this->withoutVite()->get($url)->assertOk()->getContent();

            $this->assertStringNotContainsString('data-carousel', $html, $url);
            // La lista de lo que incluye cada plan va siempre a la vista, sin botón que la abra
            $this->assertStringNotContainsString('<details class="site-plan__more"', $html, $url);
            $this->assertStringContainsString('class="site-plan__features"', $html, $url);
        }

        $css = file_get_contents(resource_path('css/site/site.css'));
        $this->assertDoesNotMatchRegularExpression('/\.site-plans[^{}]*\{[^}]*(overflow-x|scroll-snap-type)/', $css);
        // Las reglas del celular llevan el prefijo del contenedor para ganarle a las de base (misma especificidad, van después)
        $this->assertStringContainsString('.site-plans .site-plan__amount', $css);

        $js = file_get_contents(resource_path('js/site.js'));
        $this->assertStringNotContainsString('initCarousels', $js);
    }

    public function test_the_features_and_the_full_comparison_fold_on_phones_but_stay_open_on_desktop(): void
    {
        $html = $this->withoutVite()->get('/precios')->assertOk()->getContent();

        $this->assertStringContainsString('<details class="site-compare-more mt-8 lg:mt-12" open data-accordion="closed">', $html);
        $this->assertStringContainsString('site-table site-table--stack', $html);
        // Cada celda de la tabla apilada lleva el nombre de su columna para mostrarlo en el celular
        $this->assertStringContainsString('data-label="', $html);

        $js = file_get_contents(resource_path('js/site.js'));
        $this->assertStringContainsString("dataset.accordion !== 'closed'", $js);
        $this->assertStringContainsString('wide.matches || group === firstOpen', $js);
    }

    public function test_the_scrollable_tables_are_positioned_so_hidden_labels_cannot_widen_the_page(): void
    {
        // Un «sr-only» (absoluto) dentro de un contenedor con overflow pero sin posición se escapa y ensancha la página: el celular la achica
        $site = file_get_contents(resource_path('css/site/site.css'));
        $home = file_get_contents(resource_path('css/site/home.css'));

        $this->assertMatchesRegularExpression('/\.site-compare \{[^}]*position: relative;/', $site);
        $this->assertMatchesRegularExpression('/\.site-table \{[^}]*position: relative;/', $home);
        $this->assertMatchesRegularExpression('/@media \(max-width: 1023px\) \{\s*\.site-plans \{\s*position: relative;/', $site);
    }

    public function test_the_home_merges_how_it_works_with_what_it_includes_and_keeps_five_questions(): void
    {
        $html = $this->withoutVite()->get('/')->assertOk()->getContent();

        // Cuatro pasos y siete cosas que incluir, en un solo bloque y sin acordeones; sin franja de tipos de evento
        $how = Str::between($html, 'id="incluye"', 'id="precios"');
        $this->assertSame(4, substr_count($how, 'class="site-step"'));
        $this->assertSame(7, substr_count($how, 'class="site-include__item"'));
        $this->assertStringNotContainsString('<details', $how);
        $this->assertStringNotContainsString('data-accordion', $html);
        $this->assertStringNotContainsString('site-marquee', $html);

        // Solo las preguntas que deciden una compra
        $this->assertSame(5, substr_count($html, 'class="site-faq group"'));
    }

    // ── M11 · Canónica, saltar al contenido, descripciones e indexación

    public function test_every_site_page_declares_its_own_canonical_without_campaign_parameters(): void
    {
        config(['app.url' => 'https://bida-events.com']);

        $this->withoutVite()->get('/precios?utm_source=facebook&ref=feria')->assertOk()
            ->assertSee('<link rel="canonical" href="https://bida-events.com/precios">', false)
            ->assertDontSee('utm_source=facebook&amp;ref=feria" hreflang', false);

        $this->withoutVite()->get('/en/pricing?utm_source=x')->assertOk()
            ->assertSee('<link rel="canonical" href="https://bida-events.com/en/pricing">', false);

        $this->withoutVite()->get('/?utm_campaign=x')->assertOk()
            ->assertSee('<link rel="canonical" href="https://bida-events.com">', false);

        $this->withoutVite()->get('/invitaciones-de-boda?ref=abc')->assertOk()
            ->assertSee('<link rel="canonical" href="https://bida-events.com/invitaciones-de-boda">', false);
    }

    public function test_the_language_alternates_carry_no_campaign_parameters(): void
    {
        $html = $this->withoutVite()->get('/precios?utm_source=facebook')->assertOk()->getContent();

        $this->assertStringContainsString('<link rel="alternate" hreflang="en" href="'.url('/en/pricing').'">', $html);
        $this->assertStringNotContainsString('rel="alternate" hreflang="en" href="'.url('/en/pricing').'?', $html);
        // El selector de idioma sí conserva el origen de la campaña
        $this->assertStringContainsString(url('/en/pricing').'?utm_source=facebook', $html);
    }

    public function test_there_is_a_skip_link_to_the_main_content(): void
    {
        foreach (['/', '/precios', '/unete', '/guia-invitaciones-digitales', '/legal/privacidad', '/invitaciones-de-boda', '/login'] as $path) {
            $this->withoutVite()->get($path)->assertOk()
                ->assertSee('<a href="#contenido" class="site-skip">Saltar al contenido</a>', false)
                ->assertSee('id="contenido" tabindex="-1"', false);
        }

        $this->withoutVite()->get('/en')->assertOk()->assertSee('>Skip to content</a>', false);
    }

    public function test_the_sign_in_pages_have_their_own_description_and_the_right_indexing(): void
    {
        $descriptions = [];

        foreach (['/login' => false, '/contrasena/olvide' => false, '/registro' => true] as $path => $indexable) {
            $html = $this->withoutVite()->get($path)->assertOk()->getContent();
            preg_match('/<meta name="description" content="([^"]+)"/', $html, $found);
            $descriptions[$path] = html_entity_decode($found[1]);

            if ($indexable) {
                $this->assertStringNotContainsString('noindex', $html);
                $this->assertStringContainsString('rel="canonical"', $html);
            } else {
                $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $html);
                $this->assertStringNotContainsString('rel="canonical"', $html);
            }
        }

        $this->assertCount(3, array_unique($descriptions), 'cada página tiene su descripción');
        $this->assertStringContainsString('Crea tu cuenta gratis', $descriptions['/registro']);

        $this->withoutVite()->get('/contrasena/nueva/token-cualquiera')->assertOk()->assertSee('noindex, nofollow', false);
    }

    public function test_the_legal_pages_have_descriptions_long_enough_to_be_useful(): void
    {
        foreach (['privacidad', 'cookies', 'terminos'] as $page) {
            $html = $this->withoutVite()->get("/legal/{$page}")->assertOk()->getContent();
            preg_match('/<meta name="description" content="([^"]+)"/', $html, $found);

            $length = mb_strlen(html_entity_decode($found[1]));
            $this->assertGreaterThanOrEqual(120, $length, "{$page}: descripción de {$length} caracteres");
            $this->assertLessThanOrEqual(175, $length, "{$page}: descripción de {$length} caracteres");
        }
    }

    // ── M7 · Invitaciones más livianas y legibles

    public function test_the_video_player_and_the_animated_icons_load_only_when_needed(): void
    {
        $app = file_get_contents(resource_path('js/app.js'));

        $this->assertStringContainsString("whenNear('[data-video-player]'", $app);
        $this->assertStringContainsString('whenIdle(() => {', $app);
        // Ya no se descargan al abrir la página
        $this->assertDoesNotMatchRegularExpression("/^\\s*import\\('\\.\\/video-player\\.js'\\)/m", Str::before($app, 'whenNear('));
    }

    public function test_no_invitation_text_is_smaller_than_twelve_pixels(): void
    {
        $small = [];

        foreach (['invitation', 'cards'] as $folder) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path("css/{$folder}")));

            foreach ($iterator as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'css') {
                    continue;
                }

                preg_match_all('/font-size:\s*([0-9]*\.?[0-9]+)(rem|px)\b/', file_get_contents($file->getPathname()), $matches, PREG_SET_ORDER);

                foreach ($matches as $match) {
                    $px = $match[2] === 'rem' ? (float) $match[1] * 16 : (float) $match[1];

                    if ($px < 12) {
                        $small[] = basename($file->getPathname()).': '.$match[0];
                    }
                }
            }
        }

        $this->assertSame([], array_slice($small, 0, 10), 'Letra de menos de 12 px (0,75rem) en las plantillas');
    }

    public function test_the_floating_menu_button_hides_while_scrolling_down(): void
    {
        $slug = 'menu-flotante';
        $invitation = $this->createInvitationForMenu($slug);

        $this->withoutVite()->get(route('invitation.show', $invitation->slug))->assertOk()
            ->assertSee("'is-tucked': tucked && ! open", false)
            ->assertSee('trackScroll()', false);

        $css = file_get_contents(resource_path('css/invitation/nav-player.css'));
        $this->assertMatchesRegularExpression('/\.inv-nav__toggle\.is-tucked\s*\{\s*translate:/', $css);
    }

    private function createInvitationForMenu(string $slug): \App\Models\Invitation
    {
        $this->seed(\Database\Seeders\EventTypeSeeder::class);

        return \App\Models\Invitation::create([
            'event_type_id' => \App\Models\EventType::query()->value('id'),
            'slug' => $slug,
            'template' => 'invitations.templates.xv-premium',
            'title' => 'Prueba',
            'event_date' => now()->addMonths(2),
            'status' => 'active',
            'expires_at' => now()->addMonths(8),
        ]);
    }
}
