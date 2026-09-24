<?php

namespace Tests\Feature;

use App\Http\Controllers\EventLandingController;
use App\Services\InvitationModuleService;
use App\Support\LeadSource;
use Database\Seeders\ShowcaseInvitationsSeeder;
use Database\Seeders\XvSofiaModuleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Puntos 26, 27 y 28 del mapa: la vista previa al compartir por WhatsApp, las páginas por
 * tipo de evento y el código que dice de qué campaña llegó cada cliente.
 */
class SharingAndCampaignsTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    // ── 26. Vista previa al compartir ────────────────────────────────────────

    public function test_an_invitation_shares_its_name_date_place_and_cover_photo(): void
    {
        $invitation = $this->createInvitation(['slug' => 'xv-sofia']);
        app(InvitationModuleService::class)->syncAllModules($invitation, XvSofiaModuleData::all());

        $html = $this->withoutVite()->get(route('invitation.show', 'xv-sofia'))->assertOk()->getContent();

        $this->assertStringContainsString('<meta property="og:title" content="Sofía Valentina · Celebrando mis XV Años">', $html);
        $this->assertMatchesRegularExpression('/<meta property="og:description" content="[^"]+ · Salón Imperial La Paz">/', $html);
        // La portada, recortada por Cloudinary a 1200×630 y en JPG (WhatsApp no siempre lee WebP)
        $this->assertMatchesRegularExpression('#<meta property="og:image" content="https://res\.cloudinary\.com/[^"]+/image/upload/c_fill,g_auto,w_1200,h_630,q_auto,f_jpg/[^"]+">#', $html);
        $this->assertStringContainsString('<meta property="og:url" content="'.route('invitation.show', 'xv-sofia').'">', $html);
        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $html);
    }

    public function test_the_personal_link_names_the_guest_but_the_demo_does_not(): void
    {
        $invitation = $this->createInvitation(['slug' => 'xv-personal']);
        app(InvitationModuleService::class)->syncAllModules($invitation, XvSofiaModuleData::all());
        $guest = $invitation->guests()->create(['name' => 'Familia Rojas', 'passes_allocated' => 2]);

        $this->withoutVite()
            ->get(route('invitation.guest', ['slug' => 'xv-personal', 'token' => $guest->qr_code_token]))
            ->assertOk()
            ->assertSee('<meta property="og:title" content="Sofía Valentina · Invitación para Familia Rojas">', false);

        $this->seed(ShowcaseInvitationsSeeder::class);

        // La muestra de la home tiene un invitado ficticio: no debe aparecer al compartirla
        $this->withoutVite()
            ->get(route('invitation.demo', 'xv-isabella'))
            ->assertOk()
            ->assertDontSee('Invitación para Familia Pérez', false);
    }

    public function test_an_invitation_without_cover_photo_shares_the_bida_logo(): void
    {
        $invitation = $this->createInvitation(['slug' => 'xv-sin-foto']);
        $modules = XvSofiaModuleData::all();
        $modules['bienvenida']['imagen_hero'] = '';
        app(InvitationModuleService::class)->syncAllModules($invitation, $modules);

        $this->withoutVite()
            ->get(route('invitation.show', 'xv-sin-foto'))
            ->assertOk()
            ->assertSee('<meta property="og:image" content="'.asset('images/share/bida.jpg').'">', false);
    }

    public function test_an_invitation_with_cover_photo_shares_that_photo(): void
    {
        $invitation = $this->createInvitation(['slug' => 'xv-con-foto']);
        $modules = XvSofiaModuleData::all();
        $modules['bienvenida']['imagen_hero'] = 'https://res.cloudinary.com/demo/image/upload/v1/bida/portada.jpg';
        app(InvitationModuleService::class)->syncAllModules($invitation, $modules);

        $this->withoutVite()
            ->get(route('invitation.show', 'xv-con-foto'))
            ->assertOk()
            ->assertSee('res.cloudinary.com/demo/image/upload/', false)
            ->assertSee('bida/portada.jpg', false)
            ->assertDontSee('images/share/bida.jpg', false);
    }

    public function test_the_site_shares_the_bida_logo(): void
    {
        $path = public_path('images/share/bida.jpg');
        $this->assertFileExists($path);
        [$width, $height] = getimagesize($path);
        $this->assertSame([1200, 630], [$width, $height]);

        $this->withoutVite()
            ->get(route('home'))
            ->assertOk()
            ->assertSee('<meta property="og:image" content="'.asset('images/share/bida.jpg').'">', false);

        $this->artisan('bida:imagenes-compartir')->assertSuccessful();
    }

    // ── 27. Páginas por tipo de evento ───────────────────────────────────────

    public function test_every_event_page_has_its_demo_questions_and_share_card(): void
    {
        $this->seed(ShowcaseInvitationsSeeder::class);

        foreach (config('bida.landings') as $slug => $landing) {
            $this->withoutVite()
                ->get(route('landing', $slug))
                ->assertOk()
                ->assertSee('<title>'.e($landing['title']).' | '.config('bida.brand').'</title>', false)
                ->assertSee(e($landing['heading']), false)
                // Muestra embebida y su apertura
                ->assertSee(route('invitation.demo', EventLandingController::demoSlugs($landing, $slug)[0]), false)
                // Preguntas marcadas para buscadores
                ->assertSee('"@type":"FAQPage"', false)
                ->assertSee(e($landing['faqs'][0][0]), false)
                ->assertSee('<meta property="og:image" content="'.asset('images/share/bida.jpg').'">', false)
                // Enlazado entre páginas de evento
                ->assertSee(route('landing', collect(config('bida.landings'))->keys()->reject(fn ($key) => $key === $slug)->first()), false);
        }
    }

    public function test_unknown_event_pages_do_not_exist_and_the_sitemap_lists_only_public_pages(): void
    {
        $this->get('/invitaciones-de-nada')->assertNotFound();

        $response = $this->get(route('sitemap'))->assertOk();
        $this->assertStringStartsWith('application/xml', $response->headers->get('Content-Type'));

        foreach (array_keys(config('bida.landings')) as $slug) {
            $response->assertSee('<loc>'.route('landing', $slug).'</loc>', false);
        }

        $response->assertDontSee('/p/', false)->assertDontSee('/muestra/', false);
    }

    // ── 28. Origen de cada contacto ──────────────────────────────────────────

    public function test_the_whatsapp_message_carries_the_page_and_campaign_code(): void
    {
        config(['bida.whatsapp' => '59170000000']);

        $response = $this->withoutVite()
            ->get(route('landing', 'invitaciones-de-boda').'?utm_source=facebook&utm_medium=anuncio&utm_campaign=Mayo 2026')
            ->assertOk()
            ->assertCookie(LeadSource::COOKIE);

        $response->assertSee(rawurlencode("\n\nRef. BODA-FB-MAYO2026"), false);

        // Sin campaña, el código dice al menos desde qué página escribió
        $this->withoutVite()
            ->get(route('home'))
            ->assertOk()
            ->assertSee(rawurlencode("\n\nRef. WEB"), false);
    }

    public function test_the_origin_is_remembered_on_a_later_visit(): void
    {
        $this->withoutVite()
            ->withCookie(LeadSource::COOKIE, json_encode(['source' => 'feria-cbba', 'medium' => '', 'campaign' => '']))
            ->get(route('landing', 'invitaciones-xv-anos'))
            ->assertOk()
            ->assertSee(rawurlencode('Ref. XV-FERIACBBA'), false)
            // Una visita sin parámetros no reescribe el origen guardado
            ->assertCookieMissing(LeadSource::COOKIE);
    }

    public function test_the_campaign_link_command_shows_the_url_and_the_code(): void
    {
        $this->artisan('bida:enlace-campana', [
            'pagina' => 'invitaciones-de-bautizo',
            '--fuente' => 'instagram',
            '--medio' => 'historia',
            '--campana' => 'junio',
        ])
            ->expectsOutputToContain('utm_source=instagram&utm_medium=historia&utm_campaign=junio')
            ->expectsOutputToContain('Ref. BAUT-IG-JUNIO')
            ->assertSuccessful();

        $this->artisan('bida:enlace-campana', ['pagina' => 'no-existe', '--fuente' => 'facebook'])->assertFailed();
    }
}
