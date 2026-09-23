<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\InvitationTemplates;
use App\Support\Offers;
use App\Support\SiteSettings;
use Database\Seeders\ShowcaseInvitationsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Ajustes: el administrador cambia precios, promociones y qué tarjetas de temporada se ofrecen,
 * y eso se ve en la página pública sin tocar el código ni el .env.
 */
class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
        SiteSettings::forget();
    }

    protected function tearDown(): void
    {
        SiteSettings::forget();

        parent::tearDown();
    }

    public function test_only_the_admin_opens_the_settings(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.settings'))->assertForbidden();

        $this->actingAs($this->admin)
            ->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('Precios de los paquetes')
            ->assertSee('Temporada de tarjetas')
            ->assertSee('Plantillas de temporada')
            // Las tres plantillas de temporada, con su nombre humano
            ->assertSee('Carta que florece')
            ->assertSee('Libro de aventuras')
            ->assertSee('Bajo la misma luna');
    }

    public function test_the_prices_saved_in_the_panel_are_the_ones_the_page_shows(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), $this->payload([
                'packages' => [
                    'basico' => ['price' => 250, 'promo_price' => 199],
                    'estandar' => ['price' => 450, 'promo_price' => ''],
                    'premium' => ['price' => 800, 'promo_price' => 650],
                ],
            ]))
            ->assertRedirect()
            ->assertSessionHas('success');

        SiteSettings::apply();
        $packages = collect(Offers::packages())->keyBy('key');

        $this->assertSame(199, $packages['basico']['final_price']);
        $this->assertSame(250, $packages['basico']['old_price']);
        // Sin descuento se cobra el precio normal y no se tacha nada
        $this->assertSame(450, $packages['estandar']['final_price']);
        $this->assertNull($packages['estandar']['old_price']);

        // Y así se ven: el precio normal tachado y el de hoy al lado
        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder(['250 Bs', '199', 'Bs', '450', 'Bs', '800 Bs', '650'])
            ->assertSee('Ahorras 150 Bs');
    }

    public function test_the_promotion_ends_by_itself_on_the_chosen_date(): void
    {
        $this->travelTo(Carbon::parse('2026-10-01 10:00:00', 'America/La_Paz'));

        $this->actingAs($this->admin)->put(route('admin.settings.update'), $this->payload([
            'promo_active' => '1',
            'promo_ends_at' => '2026-10-05T23:59',
        ]))->assertRedirect();

        SiteSettings::apply();
        $this->assertTrue(Offers::launchPromoActive());

        // Pasada la fecha, los precios vuelven solos a los normales
        $this->travelTo(Carbon::parse('2026-10-06 10:00:00', 'America/La_Paz'));
        $this->assertFalse(Offers::launchPromoActive());
        $this->assertNull(Offers::packages()[0]['old_price']);
    }

    public function test_a_seasonal_template_that_is_switched_off_stops_being_offered(): void
    {
        $this->seed(ShowcaseInvitationsSeeder::class);
        $this->travelTo(Carbon::parse('2026-09-19 12:00:00', 'America/La_Paz'));

        // Todas encendidas: la temporada muestra sus tres diseños
        $this->actingAs($this->admin)->put(route('admin.settings.update'), $this->payload([
            'season_ends_at' => '2026-09-21T23:59',
            'templates' => array_keys(InvitationTemplates::all()),
        ]));

        SiteSettings::apply();
        $this->assertCount(3, Offers::season()['demos']);

        // Al apagar el libro de aventuras, su muestra deja de aparecer
        $this->actingAs($this->admin)->put(route('admin.settings.update'), $this->payload([
            'season_ends_at' => '2026-09-21T23:59',
            'templates' => [InvitationTemplates::TARJETA_AMOR, InvitationTemplates::WE_STORY_TOGETHER],
        ]));

        SiteSettings::apply();
        $labels = collect(Offers::season()['demos'])->pluck('label');

        $this->assertCount(2, $labels);
        $this->assertFalse($labels->contains('Libro de aventuras'));

        $this->withoutVite()->get(route('home'))->assertOk()->assertDontSee('Libro de aventuras');
    }

    public function test_a_discount_that_is_not_cheaper_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.settings'))
            ->put(route('admin.settings.update'), $this->payload([
                'packages' => [
                    'basico' => ['price' => 200, 'promo_price' => 200],
                    'estandar' => ['price' => 400, 'promo_price' => 300],
                    'premium' => ['price' => 700, 'promo_price' => 500],
                ],
                'season_price' => 100,
                'season_promo_price' => 120,
            ]))
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHasErrors(['packages.basico.promo_price', 'season_promo_price']);

        SiteSettings::apply();
        $this->assertSame(150, Offers::packages()[0]['final_price']);
    }

    /** Lo que manda el formulario completo; cada prueba cambia solo lo suyo. */
    private function payload(array $changes = []): array
    {
        return array_replace([
            'promo_active' => '1',
            'promo_ends_at' => '',
            'packages' => [
                'basico' => ['price' => 200, 'promo_price' => 150],
                'estandar' => ['price' => 400, 'promo_price' => 300],
                'premium' => ['price' => 700, 'promo_price' => 500],
            ],
            'season_price' => 100,
            'season_promo_price' => 75,
            'season_ends_at' => '2026-09-21T23:59',
            'templates' => array_keys(InvitationTemplates::all()),
        ], $changes);
    }
}
