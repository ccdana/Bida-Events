<?php

namespace Tests\Feature;

use App\Models\EventType;
use App\Models\Invitation;
use App\Models\User;
use Database\Seeders\XvSofiaModuleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationEditorTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected EventType $eventType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->eventType = EventType::create(['name' => 'XV Años', 'slug' => 'xv-anos']);
    }

    public function test_admin_saves_an_invitation_with_its_modules(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.invitations.store'), $this->payload(XvSofiaModuleData::all()));

        $invitation = Invitation::where('slug', 'xv-editor')->firstOrFail();

        $response->assertRedirect(route('admin.invitations.edit', $invitation));
        $this->assertDatabaseCount('invitation_itinerary_items', 6);
        $this->assertDatabaseCount('invitation_polls', 4);
        $this->assertSame('Sofía Valentina', $invitation->modules['bienvenida']['nombre_quinceanera']);
    }

    public function test_invalid_modules_are_rejected_and_the_editor_keeps_the_submitted_state(): void
    {
        $modules = XvSofiaModuleData::all();
        $modules['encuestas']['preguntas'][0]['tipo'] = 'desconocido';
        $modules['itinerario']['eventos'] = array_fill(0, 51, ['hora' => '20:00', 'titulo' => 'Momento']);
        $modules['bienvenida']['nombre_quinceanera'] = 'Nombre sin guardar';

        $payload = $this->payload($modules);
        $payload['modulos']['galeria'] = '{json roto';

        $this->actingAs($this->admin)
            ->from(route('admin.invitations.create'))
            ->post(route('admin.invitations.store'), $payload)
            ->assertRedirect(route('admin.invitations.create'))
            ->assertSessionHasErrors([
                'modulos_data.encuestas.preguntas.0.tipo',
                'modulos_data.itinerario.eventos',
                'modulos_data.galeria',
            ]);

        $this->assertDatabaseCount('invitations', 0);
        $this->assertNull(session()->getOldInput('modulos_data'));

        $this->withoutVite()
            ->get(route('admin.invitations.create'))
            ->assertOk()
            ->assertSee('No se guardaron los cambios')
            ->assertSee('Nombre sin guardar');
    }

    protected function payload(array $modules): array
    {
        return [
            'title' => 'XV de prueba',
            'slug' => 'xv-editor',
            'template' => 'invitations.templates.xv-premium',
            'event_type_id' => $this->eventType->id,
            'user_id' => '',
            'event_date' => now()->addMonths(2)->format('Y-m-d H:i'),
            'status' => 'active',
            'expires_at' => now()->addYear()->toDateString(),
            'modulos' => array_map(fn ($module) => json_encode($module), $modules),
        ];
    }
}
