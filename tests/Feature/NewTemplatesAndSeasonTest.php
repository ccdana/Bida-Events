<?php

namespace Tests\Feature;

use App\EventProfiles\EventProfiles;
use App\Models\User;
use App\Services\InvitationModuleService;
use App\Support\InvitationTemplates;
use App\Support\Offers;
use App\Support\ResellerSubscription;
use App\Support\SiteSettings;
use Carbon\Carbon;
use Database\Seeders\ShowcaseInvitationsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Plantillas nuevas: «Lienzo» (en blanco, para cualquier evento), «Birrete al aire» (graduación) y
 * «Noche de calabazas» (Halloween, la temporada vigente), con su página de campaña y sus ajustes.
 */
class NewTemplatesAndSeasonTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public function test_the_blank_template_is_white_with_black_text_and_no_cover(): void
    {
        $meta = InvitationTemplates::get(InvitationTemplates::LIENZO);

        $this->assertSame('#FFFFFF', $meta['palette']['background']);
        $this->assertSame('#111111', $meta['palette']['text']);
        $this->assertSame('lienzo', $meta['collection']);

        $data = ShowcaseInvitationsSeeder::data('lienzo-casa-molina');
        $invitation = $this->createInvitation(['slug' => 'lienzo-prueba', 'template' => InvitationTemplates::LIENZO]);
        app(InvitationModuleService::class)->syncAllModules($invitation, $data['modules']);

        $this->withoutVite()->get(route('invitation.show', $invitation->slug))
            ->assertOk()
            ->assertSee('inv-page inv-lienzo', false)
            ->assertSee('--bg-color: #FFFFFF', false)
            ->assertSee('Casa Molina')
            // Sin apertura: la invitación se ve de entrada
            ->assertDontSee('data-cover-trigger', false);

        // Un profesional que empieza (plan Inicial) solo tiene la plantilla en blanco
        $this->assertSame([InvitationTemplates::LIENZO], array_keys(ResellerSubscription::allowedTemplates(User::factory()->reseller('inicial')->make())));
    }

    public function test_the_graduation_opens_with_its_diploma_and_shows_the_promotion_year(): void
    {
        $data = ShowcaseInvitationsSeeder::data('graduacion-mariana');
        $invitation = $this->createInvitation(['slug' => 'grad-prueba', 'template' => InvitationTemplates::GRADUACION_BIRRETE, 'event_date' => '2026-12-12 19:00:00']);
        app(InvitationModuleService::class)->syncAllModules($invitation, $data['modules']);

        $this->withoutVite()->get(route('invitation.show', $invitation->slug))
            ->assertOk()
            ->assertSee('inv-page inv-graduacion', false)
            ->assertSee('graduationIntro()', false)
            ->assertSee('Toca la cinta para abrir el diploma')
            ->assertSee('Licenciatura en Arquitectura')
            ->assertSeeInOrder(['Promoción', '2026'])
            ->assertSee('Padrinos de promoción');

        $this->assertSame('Graduación', app(EventProfiles::class)->forTemplate(InvitationTemplates::GRADUACION_BIRRETE)->label());
    }

    public function test_halloween_is_a_seasonal_party_invitation(): void
    {
        $profile = app(EventProfiles::class)->forTemplate(InvitationTemplates::HALLOWEEN_CALABAZAS);

        $this->assertSame('halloween', $profile->season());
        $this->assertSame('invitation', $profile->kind());
        $this->assertContains('rsvp', $profile->modules());

        $data = ShowcaseInvitationsSeeder::data('halloween-noche-diego');
        $invitation = $this->createInvitation(['slug' => 'hw-prueba', 'template' => InvitationTemplates::HALLOWEEN_CALABAZAS]);
        app(InvitationModuleService::class)->syncAllModules($invitation, $data['modules']);

        $this->withoutVite()->get(route('invitation.show', $invitation->slug))
            ->assertOk()
            ->assertSee('inv-page inv-halloween', false)
            ->assertSee('halloweenIntro()', false)
            ->assertSee('Toca la calabaza para encenderla')
            ->assertSee('La noche de Diego')
            ->assertSee('Disfraz obligatorio');

        // Se puede apagar desde Ajustes como las demás plantillas de temporada
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->withoutVite()->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('Noche de calabazas');
    }

    public function test_the_halloween_page_sells_at_the_season_price_until_the_season_ends(): void
    {
        config(['bida.seasons.halloween.ends_at' => '2026-10-31 23:59:59']);
        $this->seed(ShowcaseInvitationsSeeder::class);
        $this->travelTo(Carbon::parse('2026-10-20 10:00', 'America/La_Paz'));

        $this->withoutVite()->get(route('landing', 'invitaciones-de-halloween'))
            ->assertOk()
            ->assertSee(route('invitation.demo', 'halloween-noche-diego'), false)
            ->assertSeeInOrder(['US$ 26', '20', 'USD por invitación'])
            ->assertSee('La quiero por US$ 20')
            ->assertDontSee('Elige tu paquete');

        // Mientras se vende Halloween, la página del Día del Amor ya no ofrece su precio
        $this->withoutVite()->get(route('landing', 'tarjetas-dia-del-amor'))
            ->assertOk()
            ->assertDontSee('La quiero por')
            ->assertSee('La temporada terminó');

        $this->travelTo(Carbon::parse('2026-11-01 08:00', 'America/La_Paz'));

        $this->withoutVite()->get(route('landing', 'invitaciones-de-halloween'))
            ->assertOk()
            ->assertDontSee('La quiero por US$ 20')
            ->assertSee('La temporada terminó');
    }

    public function test_the_settings_of_one_season_do_not_carry_over_to_the_next(): void
    {
        config(['bida.seasons.halloween.ends_at' => '2026-10-31 23:59:59']);
        $this->seed(ShowcaseInvitationsSeeder::class);
        $this->travelTo(Carbon::parse('2026-10-20 10:00', 'America/La_Paz'));

        // Guardado en septiembre, cuando había una sola temporada (el Día del Amor): no toca a Halloween
        SiteSettings::put('season', ['price' => 100, 'promo_price' => 75, 'ends_at' => '2026-09-30 23:59:00']);
        SiteSettings::apply();

        $this->assertSame(20, Offers::season('halloween')['final_price']);
        $this->assertSame('2026-10-31', Offers::season('halloween')['endsAt']->toDateString());
        $this->assertSame(75, config('bida.seasons.amor.promo_price'));
        $this->assertSame('2026-09-30 23:59:00', config('bida.seasons.amor.ends_at'));

        // Cada temporada se guarda por su clave
        SiteSettings::put('season', ['halloween' => ['active' => true, 'price' => 200, 'promo_price' => 150, 'ends_at' => '2026-10-31 20:00:00']]);
        SiteSettings::apply();

        $this->assertSame(150, Offers::season('halloween')['final_price']);
    }

    public function test_the_admin_switches_each_season_on_and_off_independently(): void
    {
        $this->seed(ShowcaseInvitationsSeeder::class);
        $this->travelTo(Carbon::parse('2026-10-20 10:00', 'America/La_Paz'));
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->withoutVite()->get(route('admin.settings'))
            ->assertOk()
            ->assertSeeInOrder(['Día del Amor y la Primavera', 'Carta que florece', 'Halloween', 'Noche de calabazas'])
            ->assertSee('name="seasons[amor][active]"', false)
            ->assertSee('name="seasons[halloween][active]"', false);

        $payload = [
            'promo_active' => '1',
            'packages' => [
                'basico' => ['price' => 200, 'promo_price' => 150],
                'estandar' => ['price' => 400, 'promo_price' => 300],
                'premium' => ['price' => 700, 'promo_price' => 500],
            ],
            'seasons' => [
                'amor' => ['active' => '1', 'price' => 100, 'promo_price' => 75, 'ends_at' => '2026-10-25T23:59'],
                'halloween' => ['active' => '0', 'price' => 180, 'promo_price' => 140, 'ends_at' => '2026-10-31T23:59'],
            ],
            'templates' => array_keys(InvitationTemplates::all()),
        ];

        $this->actingAs($admin)->put(route('admin.settings.update'), $payload)->assertSessionHasNoErrors();
        SiteSettings::apply();

        // Halloween apagado aunque falten días; el Día del Amor encendido hasta el 25
        $this->assertNull(Offers::season('halloween'));
        $this->assertSame('off', Offers::seasonStatus('halloween'));
        $this->assertSame(75, Offers::season('amor')['final_price']);
        $this->assertSame(['amor'], array_keys(Offers::seasons()));

        // Y al revés
        $payload['seasons']['amor']['active'] = '0';
        $payload['seasons']['halloween']['active'] = '1';
        $this->actingAs($admin)->put(route('admin.settings.update'), $payload)->assertSessionHasNoErrors();
        SiteSettings::apply();

        $this->assertSame(['halloween'], array_keys(Offers::seasons()));
    }

    public function test_a_new_invitation_takes_the_colors_and_fonts_of_its_template(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $config = $this->actingAs($admin)->withoutVite()->get(route('admin.invitations.create'))->assertOk()->viewData('editorConfig');

        $options = collect($config['templateOptions'])->keyBy('value');

        $this->assertSame('#FFFFFF', $options[InvitationTemplates::LIENZO]['palette']['background']);
        $this->assertSame('Inter', $options[InvitationTemplates::LIENZO]['fonts']['titulos']);
        $this->assertSame('Creepster', $options[InvitationTemplates::HALLOWEEN_CALABAZAS]['fonts']['script']);
        // Las plantillas de siempre no declaran letras: siguen con las del editor
        $this->assertNull($options[InvitationTemplates::XV_PREMIUM]['fonts']);
    }
}
