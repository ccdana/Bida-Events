<?php

namespace Tests\Feature;

use App\Models\EventType;
use App\Models\Invitation;
use App\Models\User;
use App\Services\InvitationModuleService;
use App\Support\InvitationTemplates;
use Database\Seeders\XvSofiaModuleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * El editor del revendedor: crea y edita lo suyo con los paneles de siempre, respeta el cupo del
 * mes y el catálogo de su plan, y no ve datos de otros clientes.
 */
class ResellerEditorTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    private User $reseller;

    private EventType $eventType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reseller = User::factory()->reseller('aliado')->create();
        $this->eventType = EventType::firstOrCreate(['slug' => 'xv-anos'], ['name' => 'XV Años']);
    }

    public function test_the_reseller_creates_an_invitation_that_is_always_its_own(): void
    {
        $someoneElse = User::factory()->create();

        $this->actingAs($this->reseller)
            ->post(route('client.invitations.store'), $this->payload(['user_id' => $someoneElse->id]))
            ->assertRedirect();

        $invitation = Invitation::where('slug', 'xv-revendedor')->firstOrFail();

        // Aunque el formulario mande otro dueño, la invitación queda a cargo de quien la crea, sin cliente
        $this->assertSame($this->reseller->id, $invitation->reseller_id);
        $this->assertNull($invitation->user_id);
        $this->assertSame(
            'Sofía Valentina',
            app(InvitationModuleService::class)->storedModules($invitation)['bienvenida']['nombre_quinceanera']
        );
    }

    public function test_the_monthly_quota_stops_new_invitations_with_a_clear_message(): void
    {
        foreach (range(1, 8) as $ignored) {
            $this->createInvitation(['reseller_id' => $this->reseller->id]);
        }

        $this->actingAs($this->reseller)
            ->from(route('client.dashboard'))
            ->post(route('client.invitations.store'), $this->payload())
            ->assertSessionHasErrors(['quota' => 'Ya usaste las 8 invitaciones que incluye tu plan este mes. El cupo vuelve a empezar el primer día del próximo mes; si necesitas más, pregúntanos por un plan mayor.']);

        $this->assertDatabaseMissing('invitations', ['slug' => 'xv-revendedor']);

        // Tampoco se abre el editor: vuelve al panel con el aviso
        $this->actingAs($this->reseller)
            ->get(route('client.invitations.create'))
            ->assertRedirect(route('client.dashboard'))
            ->assertSessionHasErrors('quota');
    }

    public function test_a_template_outside_the_plan_is_rejected(): void
    {
        config(['bida.reseller_plans.aliado.templates' => [InvitationTemplates::BODA_JARDIN]]);

        $this->actingAs($this->reseller)
            ->post(route('client.invitations.store'), $this->payload())
            ->assertSessionHasErrors(['template' => 'Esa plantilla no está incluida en tu plan.']);

        $this->assertDatabaseCount('invitations', 0);
    }

    public function test_the_editor_does_not_show_other_clients_and_talks_to_client_routes(): void
    {
        User::factory()->create(['name' => 'Cliente Ajeno Privado']);

        $response = $this->actingAs($this->reseller)
            ->withoutVite()
            ->get(route('client.invitations.create'))
            ->assertOk()
            ->assertDontSee('Cliente Ajeno Privado')
            ->assertDontSee('Sin cliente asignado');

        $config = $response->viewData('editorConfig');
        $this->assertSame([], $config['clients']->all());
        $this->assertSame('reseller', $config['editorMode']);
        $this->assertSame(route('client.editor.preview.store'), $config['previewStoreUrl']);
        $this->assertSame(route('client.editor.media.upload'), $config['mediaUploadUrl']);
        $this->assertNull($config['clientStoreUrl']);
    }

    public function test_the_reseller_edits_its_own_invitation_but_not_someone_elses(): void
    {
        $own = $this->createInvitation(['reseller_id' => $this->reseller->id, 'slug' => 'xv-propia']);
        $foreign = $this->createInvitation(['reseller_id' => User::factory()->reseller()->create()->id]);

        $this->actingAs($this->reseller)->withoutVite()->get(route('client.invitations.edit', $own))->assertOk();
        $this->actingAs($this->reseller)->get(route('client.invitations.edit', $foreign))->assertForbidden();

        $this->actingAs($this->reseller)
            ->put(route('client.invitations.update', $own), $this->payload(['slug' => 'xv-propia', 'title' => 'Nuevo título']))
            ->assertRedirect(route('client.invitations.edit', $own));
        $this->assertSame('Nuevo título', $own->fresh()->title);
        $this->assertSame($this->reseller->id, $own->fresh()->reseller_id);

        $this->actingAs($this->reseller)
            ->put(route('client.invitations.update', $foreign), $this->payload(['slug' => $foreign->slug]))
            ->assertForbidden();
    }

    public function test_an_expired_reseller_keeps_its_panel_but_cannot_create_or_edit(): void
    {
        $own = $this->createInvitation(['reseller_id' => $this->reseller->id]);
        $this->reseller->update(['subscription_renews_at' => now()->subDay()->toDateString()]);

        $this->actingAs($this->reseller)->withoutVite()->get(route('client.dashboard'))->assertOk();
        $this->actingAs($this->reseller)->get(route('client.invitation.show', $own))->assertOk();
        $this->actingAs($this->reseller)->get(route('client.invitations.edit', $own))->assertForbidden();
        $this->actingAs($this->reseller)->post(route('client.invitations.store'), $this->payload())->assertForbidden();
        // Tampoco usa las herramientas del editor (subidas, vista previa)
        $this->actingAs($this->reseller)->post(route('client.editor.preview.store'), [])->assertForbidden();
    }

    public function test_a_normal_client_does_not_reach_the_editor(): void
    {
        $client = User::factory()->create();

        $this->actingAs($client)->get(route('client.invitations.create'))->assertForbidden();
        $this->actingAs($client)->post(route('client.editor.media.upload'), [])->assertForbidden();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'XV de prueba',
            'slug' => 'xv-revendedor',
            'template' => InvitationTemplates::XV_PREMIUM,
            'event_type_id' => $this->eventType->id,
            'event_date' => now()->addMonths(2)->format('Y-m-d H:i'),
            'status' => 'active',
            'expires_at' => now()->addYear()->toDateString(),
            'modulos' => array_map(fn ($module) => json_encode($module), XvSofiaModuleData::all()),
        ], $overrides);
    }
}
