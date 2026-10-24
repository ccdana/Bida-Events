<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\InvitationModuleService;
use App\Support\Offers;
use App\Support\Packages;
use App\Support\PricingPage;
use App\Support\SiteSettings;
use Database\Seeders\XvSofiaModuleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Los precios, lado a lado: «Hazlo tú» (gratis o un pago único por nivel) y los planes. Lo a medida
 * no lleva precio: solo el aviso de escribir por WhatsApp o correo. La portada abre con armarla gratis.
 */
class PricingPageTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'auth.registration' => true,
            'payments.prepay' => [1 => 0, 3 => 10, 12 => 20],
            'bida.diy.prices' => [
                'basico' => ['price' => 9, 'promo_price' => null],
                'estandar' => ['price' => 19, 'promo_price' => null],
                'premium' => ['price' => 35, 'promo_price' => null],
            ],
        ]);
    }

    public function test_the_pricing_page_shows_the_ways_side_by_side(): void
    {
        $this->withoutVite()->get(route('pricing'))
            ->assertOk()
            ->assertSee('Precios claros, sin letra chica')
            // Hazlo tú: gratis y cada nivel con su pago único
            ->assertSeeInOrder(['id="hazlo-tu"', 'Gratis', 'Básico', '9', 'USD, pago único', 'Estándar', '19', 'Premium', '35'], false)
            ->assertSee('Crear mi invitación gratis')
            ->assertSee(route('register', ['crear' => 1]), false)
            // Planes, con el selector de ciclo
            ->assertSee('id="planes"', false)
            ->assertSee('Cada 3 meses')
            ->assertSee('20 % menos')
            // Lo a medida: solo el aviso, sin precios ni paquetes
            ->assertSee('id="a-medida"', false)
            ->assertDontSee('Te la hacemos')
            ->assertDontSee('rondas de cambios')
            // La comparación de los niveles y de las dos formas, y las preguntas
            ->assertSee('Qué trae cada nivel')
            ->assertSee('Pase QR y control de entrada')
            ->assertSee('Hazlo tú o un plan')
            ->assertSee('Profesionales')
            ->assertSee('¿Puedo empezar gratis?');
    }

    public function test_the_level_comparison_follows_the_modules_of_each_level(): void
    {
        $matrix = collect($this->withoutVite()->get(route('pricing'))->viewData('tierMatrix'))->keyBy('label');

        // Solo los niveles de pago: lo de Gratis (portada, cuenta regresiva y ubicación) está en todos
        $this->assertSame(['basico' => true, 'estandar' => true, 'premium' => true], $matrix['Itinerario y música de fondo']['tiers']);
        $this->assertSame(['basico' => false, 'estandar' => true, 'premium' => true], $matrix['Confirmación por WhatsApp']['tiers']);
        $this->assertSame(['basico' => false, 'estandar' => false, 'premium' => true], $matrix['Pase QR y control de entrada']['tiers']);
        $this->assertSame(['basico' => false, 'estandar' => false, 'premium' => true], $matrix['Reportes e impresión']['tiers']);
        $this->assertCount(8, $matrix);
    }

    public function test_the_english_plan_cards_show_the_monthly_quota_only_once(): void
    {
        // La primera línea de la lista de cada plan es el cupo y se cambia por el de hoy: en inglés no debe quedar repetida
        $html = $this->withoutVite()->get('/en/pricing')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '5 invitations per month'));
        $this->assertSame(1, substr_count($html, '20 invitations per month'));
    }

    public function test_each_level_lists_what_it_adds_to_the_previous_one_in_four_points_or_fewer(): void
    {
        $tiers = collect($this->withoutVite()->get(route('pricing'))->viewData('diyTiers'))->keyBy('key');

        foreach ($tiers as $tier) {
            $this->assertLessThanOrEqual(4, count($tier['features']), "{$tier['name']} lista de más");
        }

        $this->assertContains('Todo lo de Gratis', $tiers['basico']['features']);
        $this->assertContains('Todo lo de Básico', $tiers['estandar']['features']);
        $this->assertContains('Confirmación por WhatsApp', $tiers['estandar']['features']);
        $this->assertContains('Todo lo de Estándar', $tiers['premium']['features']);
        $this->assertContains('Pase QR y control de entrada', $tiers['premium']['features']);
    }

    public function test_create_my_invitation_goes_to_the_account_or_straight_to_the_editor(): void
    {
        // Sin cuenta: crearla, y al confirmar el correo sigue al editor de «Hazlo tú»
        $this->withoutVite()->get('/registro?crear=1')
            ->assertOk()
            ->assertSee('Con tu cuenta armas tu invitación gratis')
            ->assertSee('Publícala');
        $this->assertSame(route('client.diy.create'), session('url.intended'));

        // Con cuenta: directo al editor; un revendedor, a su panel
        $this->actingAs(User::factory()->create())->withoutVite()->get(route('pricing'))
            ->assertSee(route('client.diy.create'), false);
        $this->actingAs(User::factory()->reseller('emprendedor')->create())->withoutVite()->get(route('pricing'))
            ->assertDontSee(route('client.diy.create'), false);
    }

    public function test_the_home_opens_with_making_it_for_free_and_sums_up_the_prices(): void
    {
        $this->withoutVite()->get(route('home'))
            ->assertOk()
            // Las formas en resumen (el servicio principal es «Hazlo tú», gratis), con el detalle en /precios y el aviso de lo a medida
            ->assertSeeInOrder(['id="precios"', 'Empieza gratis. Paga solo por lo que usas.', 'Hazlo tú', 'Gratis', 'Crear mi invitación gratis', 'Planes', '¿Quieres algo a medida?', 'Ver todos los precios'], false)
            ->assertSee(route('pricing'), false)
            ->assertSee(route('register', ['crear' => 1]), false)
            ->assertDontSee('Te la hacemos');
    }

    public function test_the_service_of_each_package_is_set_in_settings(): void
    {
        SiteSettings::put('packages', ['estandar' => ['price' => 57, 'promo_price' => 43, 'delivery' => '5 días hábiles', 'revisions' => 3]]);
        SiteSettings::apply();

        $estandar = collect(Offers::packages())->firstWhere('key', 'estandar');
        $this->assertSame(['Lista en 5 días hábiles desde que nos envías tus datos', '3 rondas de cambios', 'Atención por WhatsApp'], $estandar['service_lines']);

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->withoutVite()->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('El servicio de cada paquete')
            ->assertSee('name="packages[estandar][delivery]" value="5 días hábiles"', false);
    }

    public function test_publishing_offers_custom_work_and_a_plan_when_it_pays_off(): void
    {
        $owner = User::factory()->create();
        $draft = $this->createInvitation(['user_id' => $owner->id, 'is_diy' => true, 'status' => 'inactive']);
        app(InvitationModuleService::class)->syncAllModules($draft, XvSofiaModuleData::all());

        // Lo a medida, siempre y sin precio; el plan, no con los precios de hoy (el plan Inicial cuesta menos que una Estándar)
        $this->actingAs($owner)->withoutVite()->get(route('client.diy.publish', $draft))
            ->assertOk()
            ->assertSee('¿Quieres algo a medida?')
            ->assertDontSee('terminemos')
            ->assertDontSee('¿Armas varias?');
        $this->assertNull(PricingPage::planBreakEven(Offers::resellerPlans()));

        // Con un plan que conviene desde la 2.ª y otra invitación ya publicada, se le cuenta
        config(['bida.reseller_plans.inicial.price' => 29]);
        $this->createInvitation(['user_id' => $owner->id, 'is_diy' => true, 'package' => Packages::FREE]);

        $this->actingAs($owner)->withoutVite()->get(route('client.diy.publish', $draft))
            ->assertOk()
            ->assertSee('Desde la 2.ª invitación del mes, el plan Inicial');
    }

    public function test_search_engines_find_the_pricing_page(): void
    {
        $this->get(route('sitemap'))->assertOk()->assertSee(route('pricing'), false);
        $this->get(route('llms'))->assertOk()->assertSee('Hazlo tú')->assertSee(route('pricing'), false);
    }
}
