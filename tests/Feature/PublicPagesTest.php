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
            // La página arranca en lo que ganas: sin el módulo de portada ni las tarjetas de planes, que viven en la portada
            ->assertDontSee('Haz tus propias invitaciones.')
            ->assertDontSee('site-plans--pro', false)
            ->assertSee('Un negocio de invitaciones, sin montarlo desde cero')
            ->assertSee('Lo que cobras es tuyo')
            ->assertSee('Tu marca al frente')
            ->assertSee('Entrada con QR el día del evento')
            // La comparación es donde se elige: cada plan con su precio de hoy y su descuento (Aliado: 20 → 16)
            ->assertSee('id="comparar"', false)
            ->assertSeeInOrder(['Compara los planes punto por punto', 'Única', 'US$ 5', 'Inicial', 'US$ 9', 'Aliado', 'US$ 20', 'US$ 16', 'Emprendedor', 'Agencia'])
            // La pregunta de cuánto se gana usa el cupo y el precio de hoy (9 × 25 − 16)
            ->assertSee('suman US$ 225 y te quedan US$ 209 después del plan')
            // La comparación dice qué desbloquea cada plan
            ->assertSeeInOrder(['Accesos para clientes al mes', 'Plantilla en blanco', 'Plantillas clásicas', 'Plantillas de temporada', 'Plantillas nuevas de cada evento', 'Confirmación por WhatsApp', 'Confirmación con pase QR', 'Tu marca al pie'])
            // Lo secundario: que lo haga el equipo, con los paquetes de hoy
            ->assertSeeInOrder(['Te la hacemos nosotros', 'Básico', 'US$ 22', 'Estándar', 'US$ 43', 'Premium', 'US$ 72'])
            // WhatsApp con el plan y el código de la página
            ->assertSee(rawurlencode('quiero el plan Aliado de Hazlo tú (US$ 16 al mes)'), false)
            ->assertSee(rawurlencode('Ref. HAZLO'), false);
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
