<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\SiteSettings;
use Database\Seeders\ShowcaseInvitationsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Página para profesionales (publicidad del plan para revendedores) y páginas legales, con el aviso
 * de cookies del sitio.
 */
class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_diy_page_lists_every_plan_with_its_current_price_and_discount(): void
    {
        config(['bida.whatsapp' => '+591 7123-4567']);
        $this->seed(ShowcaseInvitationsSeeder::class);

        // El administrador subió el precio del plan Aliado desde Ajustes
        SiteSettings::put('reseller_plans', ['aliado' => ['price' => 20, 'promo_price' => 16, 'quota_per_month' => 9]]);
        SiteSettings::apply();

        $this->withoutVite()->get(route('diy'))
            ->assertOk()
            ->assertSee('Tu propio panel para crear invitaciones digitales')
            // Beneficios explicados, con la entrada por QR
            ->assertSee('Qué obtienes con Hazlo tú')
            ->assertSee('Control de entrada el día del evento')
            ->assertSee('Cuentas claras')
            ->assertSeeInOrder(['Inicial', '9', 'USD al mes', 'Aliado', 'US$ 20', '16', 'USD al mes', '9 invitaciones al mes', 'Emprendedor', 'Agencia', 'Invitaciones sin tope'])
            // La comparación dice qué desbloquea cada plan
            ->assertSeeInOrder(['Accesos para clientes al mes', 'Plantilla en blanco', 'Plantillas clásicas', 'Temáticas y de temporada', 'Tu marca al pie'])
            ->assertSee('Noche de calabazas')
            // WhatsApp con el plan y el código de la página
            ->assertSee(rawurlencode('quiero el plan Aliado de Hazlo tú (US$ 16 al mes)'), false)
            ->assertSee(rawurlencode('Ref. HAZLO'), false)
            // Las muestras del teléfono se pueden abrir
            ->assertSee(route('invitation.demo', 'lienzo-casa-molina'), false);

        $this->withoutVite()->get(route('invitation.demo', 'lienzo-casa-molina'))->assertOk();
    }

    public function test_the_legal_pages_exist_and_are_linked_from_every_site_page(): void
    {
        foreach (['privacidad' => 'Política de privacidad', 'cookies' => 'Política de cookies', 'terminos' => 'Términos de uso'] as $page => $title) {
            $this->withoutVite()->get(route('legal', $page))
                ->assertOk()
                ->assertSee($title)
                ->assertSee('Actualizada el');
        }

        $this->get('/legal/otra-cosa')->assertNotFound();

        // La privacidad dice cuánto duran las fotos del fotomural y la de cookies nombra la de campaña
        $this->withoutVite()->get(route('legal', 'privacidad'))->assertSee((string) config('optimizations.retention.photos_days').' días después del evento');
        $this->withoutVite()->get(route('legal', 'cookies'))->assertSee('bida_origen');

        $this->withoutVite()->get(route('home'))
            ->assertOk()
            ->assertSee(route('legal', 'privacidad'), false)
            ->assertSee(route('legal', 'cookies'), false)
            ->assertSee(route('legal', 'terminos'), false)
            ->assertSee(route('diy'), false);
    }

    public function test_the_site_shows_the_cookie_notice_but_the_invitations_do_not(): void
    {
        $this->withoutVite()->get(route('home'))
            ->assertOk()
            ->assertSee('data-cookie-notice', false)
            ->assertSee('Ver la política de cookies');

        $this->seed(ShowcaseInvitationsSeeder::class);

        $this->withoutVite()->get(route('invitation.show', 'xv-isabella'))
            ->assertOk()
            ->assertDontSee('data-cookie-notice', false);
    }

    public function test_a_logged_in_reseller_sees_the_page_with_its_panel_link(): void
    {
        $reseller = User::factory()->reseller('aliado')->create();

        $this->actingAs($reseller)->withoutVite()->get(route('diy'))
            ->assertOk()
            ->assertSee('Mi panel');
    }
}
