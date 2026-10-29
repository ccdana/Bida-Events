<?php

namespace Tests\Feature;

use App\Models\EventType;
use App\Models\Invitation;
use App\Models\User;
use App\Services\InvitationModuleService;
use App\Services\SubscriptionActivator;
use App\Support\DiyInvitations;
use App\Support\InvitationTemplates;
use App\Support\Packages;
use Database\Seeders\XvSofiaModuleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * «Hazlo tú»: cualquier cuenta arma su invitación sin pagar antes, con todo desbloqueado. Al
 * publicarla, el nivel sale de sus partes: gratis (apagando lo que no trae, una por cuenta y con la
 * marca de Bida Events) o el nivel que piden, que todavía se activa por WhatsApp.
 */
class DiyInvitationTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    private User $owner;

    private EventType $eventType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->eventType = EventType::firstOrCreate(['slug' => 'xv-anos'], ['name' => 'XV Años']);
    }

    public function test_the_levels_come_from_the_parts_and_team_packages_do_not_change(): void
    {
        $this->assertSame(Packages::FREE, Packages::requiredTier(['bienvenida' => true, 'cuenta_regresiva' => true, 'agendar' => true, 'ubicacion' => true]));
        $this->assertSame(Packages::BASIC, Packages::requiredTier(['ubicacion' => true, 'itinerario' => true, 'musica' => true]));
        $this->assertSame(Packages::STANDARD, Packages::requiredTier(['galeria' => true, 'itinerario' => true]));
        $this->assertSame(Packages::PREMIUM, Packages::requiredTier(['rsvp' => true, 'galeria' => true]));
        // Lo apagado no cuenta, ni lo que su plantilla no usa
        $this->assertSame(Packages::FREE, Packages::requiredTier(['galeria' => false, 'rsvp' => true], ['galeria', 'ubicacion']));

        // Los paquetes del equipo siguen igual: Básico trae itinerario y música, no la galería
        $this->assertTrue(Packages::allowsModule(Packages::BASIC, 'itinerario'));
        $this->assertTrue(Packages::allowsModule(Packages::BASIC, 'bienvenida'));
        $this->assertFalse(Packages::allowsModule(Packages::BASIC, 'galeria'));
        $this->assertTrue(Packages::allowsModule(null, 'rsvp'));
        // Gratis: solo la portada, la cuenta regresiva y la ubicación
        $this->assertTrue(Packages::allowsModule(Packages::FREE, 'ubicacion'));
        $this->assertFalse(Packages::allowsModule(Packages::FREE, 'itinerario'));
    }

    public function test_a_new_account_creates_a_draft_that_belongs_to_it(): void
    {
        $someoneElse = User::factory()->create();

        // Aunque el formulario pida publicarla y mande otro dueño, nace borrador, suya y sin nivel
        $this->actingAs($this->owner)
            ->post(route('client.diy.store'), $this->payload(['user_id' => $someoneElse->id, 'status' => 'active']))
            ->assertRedirect();

        $invitation = Invitation::where('slug', 'xv-hazlo-tu')->firstOrFail();

        $this->assertTrue($invitation->is_diy);
        $this->assertSame($this->owner->id, $invitation->user_id);
        $this->assertNull($invitation->reseller_id);
        $this->assertNull($invitation->package);
        $this->assertSame('inactive', $invitation->status);
        // El vencimiento va con la fecha del evento
        $this->assertSame($invitation->event_date->copy()->startOfDay()->addDays(30)->toDateString(), $invitation->expires_at->toDateString());
        $this->assertSame('Sofía Valentina', app(InvitationModuleService::class)->storedModules($invitation)['bienvenida']['nombre_quinceanera']);
    }

    public function test_the_editor_runs_with_everything_unlocked_and_the_level_of_each_part(): void
    {
        $response = $this->actingAs($this->owner)->withoutVite()->get(route('client.diy.create'))->assertOk();

        $config = $response->viewData('editorConfig');
        $this->assertSame('diy', $config['editorMode']);
        $this->assertSame('', $config['meta']['package']);
        $this->assertSame(Packages::STANDARD, $config['diyTiers']['modules']['galeria']);
        $this->assertSame(Packages::FREE, $config['diyTiers']['modules']['ubicacion']);
        $this->assertNull($config['diyTiers']['paid']);
        $this->assertSame(route('client.editor.preview.store'), $config['previewStoreUrl']);
        $this->assertSame([], $config['clients']->all());
        // Las de temporada también se arman (mientras su temporada se vende; si no, con su motivo)
        $this->assertContains(InvitationTemplates::HALLOWEEN_CALABAZAS, $config['templateOptions']->pluck('value')->all());

        // Y usa las herramientas del editor
        $this->actingAs($this->owner)
            ->postJson(route('client.editor.preview.store'), ['modulos' => []])
            ->assertOk();
    }

    public function test_there_is_a_limit_of_drafts_and_a_draft_can_be_deleted(): void
    {
        config(['bida.diy.drafts_limit' => 2]);
        $first = $this->draft(['slug' => 'borrador-uno']);
        $this->draft(['slug' => 'borrador-dos']);

        $this->actingAs($this->owner)
            ->post(route('client.diy.store'), $this->payload())
            ->assertSessionHasErrors(['quota' => 'Ya tienes 2 invitaciones sin publicar. Publica una o borra la que no uses para crear otra.']);
        $this->actingAs($this->owner)->get(route('client.diy.create'))->assertRedirect(route('client.dashboard'));

        // Borrar un borrador libera su lugar
        $this->actingAs($this->owner)->delete(route('client.diy.destroy', $first))->assertRedirect(route('client.dashboard'));
        $this->assertModelMissing($first);
        $this->actingAs($this->owner)->post(route('client.diy.store'), $this->payload())->assertSessionHasNoErrors();
    }

    public function test_publishing_goes_through_the_publish_page_with_the_required_level(): void
    {
        $invitation = $this->draft();

        $this->actingAs($this->owner)
            ->put(route('client.diy.update', $invitation), $this->payload(['slug' => $invitation->slug, 'status' => 'active']))
            ->assertRedirect(route('client.diy.publish', $invitation));

        // «Publicar» solo guarda: sigue sin publicar hasta elegir gratis o pagar
        $this->assertSame('inactive', $invitation->fresh()->status);

        $this->actingAs($this->owner)
            ->get(route('client.diy.publish', $invitation))
            ->assertOk()
            ->assertSee('nivel <strong class="font-semibold text-site-ink">Premium</strong>', false)
            ->assertSee('Pedir Premium por WhatsApp')
            ->assertSee('Publicar gratis sin esas partes')
            ->assertSee('Confirmar asistencia')
            // Sin tarjetas: lo que incluye cada opción es una lista con marcas y el botón es el del sitio
            ->assertSee('site-checks', false)
            ->assertSee('site-btn site-btn--lg', false)
            ->assertDontSee('admin-card', false)
            ->assertDontSee('admin-primary-button', false)
            // Y sin los párrafos que repetían lo que dicen las opciones
            ->assertDontSee('Publícala así, o gratis apagando lo que la gratuita no trae.')
            ->assertDontSee('Escríbenos y activamos tu invitación con este nivel.');
    }

    public function test_publishing_free_turns_off_the_paid_parts_and_shows_the_bida_mark(): void
    {
        $invitation = $this->draft();

        $this->actingAs($this->owner)
            ->post(route('client.diy.publish.store', $invitation), ['mode' => 'free'])
            ->assertRedirect(route('client.dashboard'));

        $invitation->refresh();
        $this->assertSame('active', $invitation->status);
        $this->assertSame(Packages::FREE, $invitation->package);
        $this->assertSame($invitation->event_date->copy()->startOfDay()->addDays(7)->toDateString(), $invitation->expires_at->toDateString());

        $flags = app(InvitationModuleService::class)->resolveModules($invitation)['config']['modulos'];
        $this->assertTrue($flags['ubicacion']);
        $this->assertTrue($flags['cuenta_regresiva']);
        $this->assertFalse($flags['galeria']);
        $this->assertFalse($flags['itinerario']);
        $this->assertFalse($flags['rsvp']);
        // El contenido de lo apagado queda guardado
        $this->assertNotEmpty(app(InvitationModuleService::class)->storedModules($invitation)['galeria']['fotos']);

        $this->withoutVite()->get(route('invitation.show', $invitation->slug))
            ->assertOk()
            ->assertSee('class="inv-free-mark"', false)
            ->assertSee('Invitación gratuita hecha con');
    }

    public function test_only_one_free_invitation_per_account(): void
    {
        $this->draft(['slug' => 'la-gratuita', 'package' => Packages::FREE, 'status' => 'active']);
        $second = $this->draft(['slug' => 'la-segunda']);

        $this->actingAs($this->owner)
            ->post(route('client.diy.publish.store', $second), ['mode' => 'free'])
            ->assertSessionHasErrors(['publish' => 'Ya usaste tu invitación gratuita. Esta se publica con un nivel pagado.']);
        $this->assertSame('inactive', $second->fresh()->status);

        // Aunque solo use lo gratuito, la segunda se publica por lo menos con Básico
        $modules = $this->freeOnlyModules();
        app(InvitationModuleService::class)->syncAllModules($second, $modules);
        $this->assertSame(Packages::BASIC, DiyInvitations::publishTier($second->fresh(), $modules));

        $this->actingAs($this->owner)
            ->get(route('client.diy.publish', $second))
            ->assertOk()
            ->assertSee('Pedir Básico por WhatsApp')
            ->assertSee('Ya usaste tu invitación gratuita en otro evento')
            ->assertDontSee('Publicar gratis');
    }

    public function test_publishing_needs_a_confirmed_email_and_the_paid_level(): void
    {
        $unverified = User::factory()->unverified()->create();
        $invitation = $this->draft(['user_id' => $unverified->id]);

        $this->actingAs($unverified)
            ->post(route('client.diy.publish.store', $invitation), ['mode' => 'free'])
            ->assertSessionHasErrors(['publish' => 'Confirma tu correo para publicar tu invitación.']);
        $this->assertSame('inactive', $invitation->fresh()->status);

        // Sin el nivel que piden sus partes no se publica «tal cual»
        $draft = $this->draft(['slug' => 'sin-pagar']);
        $this->actingAs($this->owner)
            ->post(route('client.diy.publish.store', $draft), ['mode' => 'covered'])
            ->assertSessionHasErrors('publish');
        $this->assertSame('inactive', $draft->fresh()->status);
    }

    public function test_a_published_invitation_hides_what_is_above_its_level_until_it_is_upgraded(): void
    {
        // Publicada con Básico (lo activa el equipo mientras no hay pago en línea)
        $invitation = $this->draft(['package' => Packages::BASIC, 'status' => 'active']);

        $page = $this->withoutVite()->get(route('invitation.show', $invitation->slug))->assertOk();
        $page->assertDontSee('class="inv-free-mark"', false);
        $this->assertFalse(Packages::allowsModuleFor($invitation, 'galeria'));
        $this->assertTrue(Packages::allowsModuleFor($invitation, 'itinerario'));
        $this->assertFalse(Packages::allowsFor($invitation, 'personal_links'));

        // Guardar con la galería encendida no la despublica, pero avisa que falta subir de nivel
        $this->actingAs($this->owner)
            ->put(route('client.diy.update', $invitation), $this->payload(['slug' => $invitation->slug, 'status' => 'active']))
            ->assertRedirect(route('client.diy.edit', $invitation))
            ->assertSessionHas('success', 'Cambios guardados. Lo que encendiste de un nivel mayor no se verá en tu invitación hasta que la subas de nivel.');
        $this->assertSame('active', $invitation->fresh()->status);

        // «Subir a…» del editor pasa por la página de publicar
        $this->actingAs($this->owner)
            ->put(route('client.diy.update', $invitation), $this->payload(['slug' => $invitation->slug, 'status' => 'active', 'next' => 'publish']))
            ->assertRedirect(route('client.diy.publish', $invitation));
        $this->actingAs($this->owner)->get(route('client.diy.publish', $invitation))
            ->assertOk()
            ->assertSee('Sube tu invitación a Premium')
            ->assertDontSee('Publicar gratis');

        // Ocultarla siempre se puede; y volver a publicarla, si alcanza su nivel
        $this->actingAs($this->owner)
            ->put(route('client.diy.update', $invitation), $this->payload(['slug' => $invitation->slug, 'status' => 'inactive', 'modulos' => $this->encoded($this->freeOnlyModules())]))
            ->assertSessionHas('success', 'Tu invitación quedó oculta: tus invitados no la verán hasta que la publiques otra vez.');
        $this->actingAs($this->owner)
            ->post(route('client.diy.publish.store', $invitation), ['mode' => 'covered'])
            ->assertRedirect(route('client.dashboard'));
        $this->assertSame('active', $invitation->fresh()->status);
        $this->assertSame(Packages::BASIC, $invitation->fresh()->package);
    }

    public function test_only_the_owner_edits_it_and_a_published_one_is_not_deleted(): void
    {
        $invitation = $this->draft();
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->get(route('client.diy.edit', $invitation))->assertRedirect()->assertSessionHas('access_notice');
        $this->actingAs($stranger)->post(route('client.diy.publish.store', $invitation), ['mode' => 'free'])->assertRedirect()->assertSessionHas('access_notice');
        $this->actingAs($stranger)->delete(route('client.diy.destroy', $invitation))->assertRedirect()->assertSessionHas('access_notice');

        // El editor del revendedor no la abre (ahí «status» publicaría sin pasar por el nivel)
        $this->owner->update(['is_reseller' => true, 'reseller_plan' => 'inicial', 'subscription_status' => 'active', 'subscription_renews_at' => now()->addMonth()]);
        $this->actingAs($this->owner->fresh())->get(route('client.invitations.edit', $invitation))->assertRedirect()->assertSessionHas('access_notice');

        $published = $this->draft(['slug' => 'ya-publicada', 'package' => Packages::FREE, 'status' => 'active']);
        $this->actingAs($this->owner)->delete(route('client.diy.destroy', $published))->assertRedirect()->assertSessionHas('access_notice');
        $this->assertModelExists($published);
    }

    public function test_the_dashboard_invites_to_create_and_lists_the_drafts(): void
    {
        $this->actingAs($this->owner)->withoutVite()->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('Arma tu invitación, gratis')
            ->assertSee(route('client.diy.create'), false);

        $draft = $this->draft(['title' => 'Mis XV en borrador']);

        $this->actingAs($this->owner)->withoutVite()->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('Mis XV en borrador')
            ->assertSee('Borrador')
            ->assertSee(route('client.diy.edit', $draft), false)
            ->assertSee('Borrar borrador');

        // Un revendedor sigue con su propio editor
        $reseller = User::factory()->reseller()->create();
        $this->actingAs($reseller)->withoutVite()->get(route('client.dashboard'))
            ->assertOk()
            ->assertDontSee(route('client.diy.create'), false);
    }

    public function test_buying_a_plan_makes_the_accounts_diy_invitations_part_of_the_plan(): void
    {
        $draft = $this->draft(['slug' => 'borrador-previo']);
        $paid = $this->draft(['slug' => 'ya-pagada', 'package' => Packages::PREMIUM, 'status' => 'active']);
        $otherPerson = $this->createInvitation(['user_id' => User::factory()->create()->id, 'is_diy' => true, 'package' => Packages::BASIC, 'status' => 'active']);

        app(SubscriptionActivator::class)->recordPayment($this->owner, 'inicial', ['amount' => 10, 'currency' => 'USD']);

        // Ya no piden nivel ni pago: lo que trae lo dice el plan, y siguen a su nombre
        foreach ([$draft, $paid] as $invitation) {
            $invitation->refresh();
            $this->assertFalse($invitation->is_diy);
            $this->assertSame($this->owner->id, $invitation->reseller_id);
            $this->assertSame($this->owner->id, $invitation->user_id);
            $this->assertNull($invitation->package);
        }
        $this->assertSame('active', $paid->status);

        // Y las de otra cuenta, que no compró nada, no se tocan
        $this->assertTrue($otherPerson->fresh()->is_diy);
        $this->assertSame(Packages::BASIC, $otherPerson->fresh()->package);

        // El dueño ya puede editarlas con el editor del plan, sin pasar por la página de publicar
        $this->actingAs($this->owner->fresh())->withoutVite()
            ->get(route('client.invitations.edit', $draft))->assertOk();
    }

    private function draft(array $attributes = []): Invitation
    {
        $invitation = $this->createInvitation(array_merge([
            'user_id' => $this->owner->id,
            'is_diy' => true,
            'package' => null,
            'status' => 'inactive',
        ], $attributes));

        app(InvitationModuleService::class)->syncAllModules($invitation, XvSofiaModuleData::all());

        return $invitation->fresh();
    }

    /** Lo de Sofía con solo las partes gratuitas encendidas. */
    private function freeOnlyModules(): array
    {
        $modules = XvSofiaModuleData::all();
        $modules['config']['modulos'] = array_map(fn () => false, $modules['config']['modulos']);
        $modules['config']['modulos'] = array_merge($modules['config']['modulos'], ['bienvenida' => true, 'cuenta_regresiva' => true, 'ubicacion' => true, 'agendar' => true]);

        return $modules;
    }

    private function encoded(array $modules): array
    {
        return array_map(fn ($module) => json_encode($module), $modules);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'XV de prueba',
            'slug' => 'xv-hazlo-tu',
            'template' => InvitationTemplates::XV_PREMIUM,
            'event_type_id' => $this->eventType->id,
            'event_date' => now()->addMonths(2)->format('Y-m-d H:i'),
            'status' => 'inactive',
            'modulos' => $this->encoded(XvSofiaModuleData::all()),
        ], $overrides);
    }
}
