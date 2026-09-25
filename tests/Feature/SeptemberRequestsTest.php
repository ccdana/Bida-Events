<?php

namespace Tests\Feature;

use App\Models\EventType;
use App\Models\GuestContribution;
use App\Models\User;
use App\Support\ImageFrames;
use App\Support\InvitationTemplates;
use App\Support\SiteSettings;
use App\Support\TemplateAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Pedidos de fin de septiembre: contacto y redes desde Ajustes, la página de clientes del
 * administrador, el revendedor con eventos propios, los tipos de evento que ofrece el editor, la
 * página del evento del cliente, las historias y las medidas del recortador.
 */
class SeptemberRequestsTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        SiteSettings::forget();
    }

    protected function tearDown(): void
    {
        SiteSettings::forget();

        parent::tearDown();
    }

    public function test_the_admin_changes_the_whatsapp_and_the_social_networks_from_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->settingsPayload([
                'contact' => [
                    'whatsapp' => '+591 7654 3210',
                    'email' => 'hola@ejemplo.com',
                    'instagram' => 'https://www.instagram.com/mi.estudio/',
                    'facebook' => '@miestudio',
                    'tiktok' => '',
                ],
            ]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        SiteSettings::apply();

        $this->assertSame('59176543210', config('bida.whatsapp'));
        $this->assertSame('mi.estudio', config('bida.instagram'));
        $this->assertSame('miestudio', config('bida.facebook'));
        // Vacía: esa red deja de mostrarse
        $this->assertSame('', config('bida.tiktok'));

        $this->withoutVite()->get(route('home'))
            ->assertOk()
            ->assertSee('wa.me/59176543210', false)
            ->assertSee('instagram.com/mi.estudio', false)
            ->assertDontSee('tiktok.com/@', false);
    }

    public function test_a_whatsapp_without_country_code_is_rejected(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->settingsPayload([
                'contact' => ['whatsapp' => '7654321', 'email' => '', 'instagram' => '', 'facebook' => '', 'tiktok' => ''],
            ]))
            ->assertSessionHasErrors('contact.whatsapp');
    }

    public function test_the_admin_sees_the_team_clients_and_the_clients_of_every_reseller(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $reseller = User::factory()->reseller('aliado')->create(['business_name' => 'Estudio Luz']);
        $teamClient = User::factory()->create(['name' => 'Familia del Equipo']);
        $resellerClient = User::factory()->create(['name' => 'Familia del Revendedor', 'created_by_reseller_id' => $reseller->id]);
        $this->createInvitation(['user_id' => $teamClient->id, 'title' => 'Boda del equipo']);
        $this->createInvitation(['user_id' => $resellerClient->id, 'reseller_id' => $reseller->id, 'title' => 'Boda del revendedor']);

        $this->actingAs($admin)->withoutVite()->get(route('admin.clients.index'))
            ->assertOk()
            ->assertSee('Familia del Equipo')
            ->assertSee('Familia del Revendedor')
            ->assertSee('Estudio Luz')
            ->assertSee('Boda del revendedor');

        $this->actingAs($admin)->withoutVite()->get(route('admin.clients.index', ['origen' => 'equipo']))
            ->assertSee('Familia del Equipo')
            ->assertDontSee('Familia del Revendedor');

        $this->actingAs($admin)->withoutVite()->get(route('admin.clients.index', ['revendedor' => $reseller->id]))
            ->assertSee('Familia del Revendedor')
            ->assertDontSee('Familia del Equipo');

        // Un cliente o un revendedor no entran a esta página
        $this->actingAs($teamClient)->get(route('admin.clients.index'))->assertForbidden();
        $this->actingAs($reseller)->get(route('admin.clients.index'))->assertForbidden();
    }

    public function test_a_reseller_event_can_be_their_own_without_spending_a_client_access(): void
    {
        $reseller = User::factory()->reseller('aliado')->create();
        $invitation = $this->createInvitation(['reseller_id' => $reseller->id]);

        $this->actingAs($reseller)
            ->post(route('client.invitations.client.self', $invitation))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame($reseller->id, $invitation->fresh()->user_id);
        $this->assertSame(0, $reseller->resellerClients()->count());

        // Ve y maneja la lista de invitados de su propio evento
        $this->actingAs($reseller)->withoutVite()->get(route('client.invitation.show', $invitation))
            ->assertOk()
            ->assertSee('Este evento es tuyo.');
        $this->assertTrue($reseller->can('manageOwnGuests', $invitation->fresh()));

        // Deja de estar a su nombre sin que se borre su cuenta
        $this->actingAs($reseller)->delete(route('client.invitations.client.destroy', $invitation))->assertRedirect();
        $this->assertNull($invitation->fresh()->user_id);
        $this->assertModelExists($reseller);
    }

    public function test_the_editor_only_offers_what_the_admin_left_on_and_groups_the_event_types(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        EventType::firstOrCreate(['code' => 'halloween'], ['name' => 'Halloween', 'slug' => 'halloween', 'kind' => 'invitation', 'season' => 'halloween']);
        EventType::firstOrCreate(['code' => 'amor'], ['name' => 'Día del Amor', 'slug' => 'dia-del-amor', 'kind' => 'card', 'season' => 'amor']);
        config([
            'bida.seasons.halloween.active' => false,
            'bida.seasons.amor.active' => true,
            'bida.seasons.amor.ends_at' => now()->addMonth()->toDateTimeString(),
            'bida.templates_disabled' => ['invitations.templates.tarjeta-aventura'],
        ]);

        $this->assertSame('Temporada apagada en Ajustes', TemplateAvailability::templateReason('invitations.templates.halloween-calabazas'));
        $this->assertSame('Apagada en Ajustes', TemplateAvailability::templateReason('invitations.templates.tarjeta-aventura'));
        $this->assertNull(TemplateAvailability::templateReason('invitations.templates.tarjeta-amor'));
        $this->assertSame(['Eventos de todo el año', 'Primaveral y romántico', 'Tenebroso'], TemplateAvailability::categoryOrder());

        $config = $this->actingAs($admin)->withoutVite()->get(route('admin.invitations.create'))->assertOk()->viewData('editorConfig');
        $types = collect($config['eventTypes'])->keyBy('code');
        $this->assertSame('Tenebroso', $types['halloween']['category']);
        $this->assertSame('Temporada apagada en Ajustes', $types['halloween']['disabledReason']);
        $this->assertSame('Primaveral y romántico', $types['amor']['category']);

        // Guardar una invitación nueva con una plantilla apagada no se puede
        $halloween = EventType::where('code', 'halloween')->first();
        $this->actingAs($admin)
            ->post(route('admin.invitations.store'), [
                'title' => 'Fiesta', 'slug' => 'fiesta-apagada', 'template' => 'invitations.templates.halloween-calabazas',
                'event_type_id' => $halloween->id, 'event_date' => now()->addMonth()->toDateTimeString(), 'status' => 'active',
                'expires_at' => now()->addMonths(3)->toDateString(), 'modulos' => [],
            ])
            ->assertSessionHasErrors('template');

        // Una invitación que ya tenía esa plantilla se sigue pudiendo abrir y editar
        $existing = $this->createInvitation(['template' => 'invitations.templates.halloween-calabazas', 'event_type_id' => $halloween->id]);
        $config = $this->actingAs($admin)->withoutVite()->get(route('admin.invitations.edit', $existing))->assertOk()->viewData('editorConfig');
        $this->assertNull(collect($config['templateOptions'])->firstWhere('value', 'invitations.templates.halloween-calabazas')['disabledReason']);
    }

    public function test_the_client_event_page_puts_downloads_and_door_first_and_splits_photos_from_songs(): void
    {
        $client = User::factory()->create();
        $invitation = $this->createInvitation(['user_id' => $client->id, 'door_token' => str_repeat('a', 48)]);
        $invitation->contributions()->create(['type' => 'live_photo', 'file_path' => 'https://res.cloudinary.com/demo/image/upload/foto.jpg', 'created_at' => now()]);
        $invitation->contributions()->create(['type' => 'song_request', 'content_text' => 'https://youtu.be/dQw4w9WgXcQ', 'created_at' => now()]);

        $this->actingAs($client)->withoutVite()->get(route('client.invitation.show', $invitation))
            ->assertOk()
            ->assertSeeInOrder(['Descargar', 'Control de entrada', 'Cómo va tu evento', 'Mis invitados', 'Fotos de tus invitados', 'Canciones y videos que sugirieron'])
            ->assertSee('i.ytimg.com/vi/dQw4w9WgXcQ', false)
            ->assertSee('Copiar enlace en historias');

        $this->assertSame(2, GuestContribution::count());
    }

    public function test_invitations_can_be_seen_as_stories_and_cards_keep_their_own_story(): void
    {
        foreach (['xv-premium', 'boda-jardin', 'bautizo-cielo', 'cumple-fiesta', 'graduacion-birrete', 'halloween-calabazas', 'lienzo'] as $template) {
            $invitation = $this->createInvitation(['template' => "invitations.templates.{$template}"]);

            $this->withoutVite()->get(route('invitation.show', $invitation->slug))
                ->assertOk()
                ->assertSee('class="inv-tale"', false)
                ->assertSee('Ver como historia');
        }

        $card = $this->createInvitation(['template' => 'invitations.templates.tarjeta-amor']);
        $this->withoutVite()->get(route('invitation.show', $card->slug))->assertOk()->assertDontSee('class="inv-tale"', false);
    }

    public function test_every_template_has_a_real_frame_for_its_cover_photo(): void
    {
        $frames = ImageFrames::forEditor();

        foreach (array_keys(InvitationTemplates::all()) as $template) {
            $this->assertArrayHasKey($template, $frames['hero']);
            $this->assertGreaterThanOrEqual(1000, $frames['hero'][$template]['width']);
        }

        // La galería de las invitaciones es vertical 4:5 y la foto del lugar 16:10, como en la plantilla
        $this->assertSame([1080, 1350], [$frames['contexts']['gallery']['width'], $frames['contexts']['gallery']['height']]);
        $this->assertSame(1.6, $frames['contexts']['ubicacion']['width'] / $frames['contexts']['ubicacion']['height']);
        $this->assertSame('circle', $frames['hero']['invitations.templates.halloween-calabazas']['shape']);
    }

    public function test_the_showcase_invitations_have_their_own_section_in_the_admin_panel(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        config(['bida.demo_invitations' => ['muestra-xv']]);
        $this->createInvitation(['slug' => 'muestra-xv', 'title' => 'Invitación de muestra']);
        $this->createInvitation(['slug' => 'boda-de-un-cliente', 'title' => 'Boda de un cliente']);

        $this->actingAs($admin)->withoutVite()->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Boda de un cliente')
            ->assertDontSee('Invitación de muestra')
            ->assertViewHas('metrics', fn (array $metrics) => $metrics['total'] === 1);

        $this->actingAs($admin)->withoutVite()->get(route('admin.showcase'))
            ->assertOk()
            ->assertSee('Invitación de muestra')
            ->assertDontSee('Boda de un cliente')
            ->assertViewHas('filterRoute', 'admin.showcase');

        $this->actingAs(User::factory()->create())->get(route('admin.showcase'))->assertForbidden();
    }

    public function test_halloween_can_be_turned_off_and_on_again_from_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->travelTo(now()->setDate(2026, 10, 10));
        $seasons = fn (string $active) => ['seasons' => [
            'amor' => ['active' => '0', 'price' => 100, 'promo_price' => 75, 'ends_at' => '2026-09-21T23:59'],
            'halloween' => ['active' => $active, 'price' => 180, 'promo_price' => 140, 'ends_at' => '2026-10-31T23:59'],
        ]];

        $this->actingAs($admin)->put(route('admin.settings.update'), $this->settingsPayload($seasons('0')))->assertSessionHasNoErrors();
        SiteSettings::apply();
        $this->assertSame('Temporada apagada en Ajustes', TemplateAvailability::templateReason('invitations.templates.halloween-calabazas'));

        $this->actingAs($admin)->put(route('admin.settings.update'), $this->settingsPayload($seasons('1')))->assertSessionHasNoErrors();
        SiteSettings::apply();
        $this->assertNull(TemplateAvailability::templateReason('invitations.templates.halloween-calabazas'));
    }

    private function settingsPayload(array $changes = []): array
    {
        return array_replace([
            'promo_active' => '1',
            'promo_ends_at' => '',
            'packages' => [
                'basico' => ['price' => 200, 'promo_price' => 150],
                'estandar' => ['price' => 400, 'promo_price' => 300],
                'premium' => ['price' => 700, 'promo_price' => 500],
            ],
            'seasons' => [
                'amor' => ['active' => '1', 'price' => 100, 'promo_price' => 75, 'ends_at' => '2026-09-21T23:59'],
                'halloween' => ['active' => '1', 'price' => 180, 'promo_price' => 140, 'ends_at' => '2026-10-31T23:59'],
            ],
            'templates' => array_keys(InvitationTemplates::all()),
        ], $changes);
    }
}
