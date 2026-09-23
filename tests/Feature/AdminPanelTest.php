<?php

namespace Tests\Feature;

use App\Models\EventType;
use App\Models\InvitationExport;
use App\Models\User;
use App\Support\InvitationTemplates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * El panel del administrador: los eventos separados por tipo, el buscador, la ficha que evita
 * abrir el editor solo para mirar, y el borrado de una invitación con todo lo suyo.
 */
class AdminPanelTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_the_panel_separates_the_events_by_type(): void
    {
        $this->createInvitation(['title' => 'XV de Sofía']);
        $this->createInvitation(['title' => 'Boda de Marta', 'event_type_id' => $this->eventType('bodas', 'Bodas')->id]);
        $this->createInvitation([
            'title' => 'Carta para Ana',
            'event_type_id' => $this->eventType('dia-del-amor', 'Día del Amor', 'card')->id,
            'template' => InvitationTemplates::TARJETA_AMOR,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            // Cada tipo de evento tiene su sección, y las tarjetas van al final
            ->assertSeeInOrder(['Bodas', 'Boda de Marta', 'XV Años', 'XV de Sofía', 'Día del Amor', 'Carta para Ana'])
            // Y se puede filtrar por un tipo
            ->assertSee(route('admin.dashboard', ['tipo' => 'bodas']), false);

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard', ['tipo' => 'bodas']))
            ->assertOk()
            ->assertSee('Boda de Marta')
            ->assertDontSee('XV de Sofía');
    }

    public function test_the_search_finds_an_event_by_its_name_link_or_client(): void
    {
        $client = User::factory()->create(['name' => 'Familia Quispe', 'username' => 'familia.quispe']);
        $this->createInvitation(['title' => 'XV de Sofía', 'slug' => 'xv-sofia', 'user_id' => $client->id]);
        $this->createInvitation(['title' => 'Boda de Marta', 'slug' => 'boda-marta']);

        $this->actingAs($this->admin);

        foreach (['sofía', 'xv-sofia', 'familia.quispe', 'Familia Quispe'] as $term) {
            $this->get(route('admin.dashboard', ['q' => $term]))
                ->assertOk()
                ->assertSee('XV de Sofía')
                ->assertDontSee('Boda de Marta', false);
        }

        $this->get(route('admin.dashboard', ['q' => 'no existe nada así']))
            ->assertOk()
            ->assertSee('Nada coincide con esa búsqueda');
    }

    public function test_the_card_shows_the_client_and_the_numbers_without_opening_the_editor(): void
    {
        $client = User::factory()->create(['name' => 'Familia Quispe', 'username' => 'familia.quispe']);
        $invitation = $this->createInvitation(['title' => 'XV de Sofía', 'user_id' => $client->id]);
        $invitation->guests()->createMany([
            ['name' => 'Ana Quispe', 'passes_allocated' => 4, 'passes_confirmed' => 3, 'status' => 'confirmed'],
            ['name' => 'Familia Rojas', 'passes_allocated' => 2, 'status' => 'pending'],
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Familia Quispe')
            ->assertSee('familia.quispe')
            ->assertSee('Noche de gala')
            ->assertSee(route('invitation.show', $invitation->slug), false)
            // Invitados: en la lista, confirmados, sin responder y personas confirmadas
            ->assertSeeInOrder(['En la lista', '2', 'Confirmaron', '1', 'Sin responder', '1', 'Personas', '3'])
            ->assertSee('Eliminar invitación');
    }

    public function test_the_admin_deletes_an_invitation_with_everything_of_its_own(): void
    {
        Storage::fake('local');
        $invitation = $this->createInvitation(['title' => 'XV de Sofía']);
        $guest = $invitation->guests()->create(['name' => 'Ana Quispe', 'passes_allocated' => 2]);
        $export = InvitationExport::create([
            'invitation_id' => $invitation->id,
            'user_id' => $this->admin->id,
            'type' => 'guests-excel',
            'status' => InvitationExport::READY,
            'path' => "exports/{$invitation->id}/invitados.xlsx",
        ]);
        Storage::disk('local')->put($export->path, 'archivo');

        $this->actingAs($this->admin)
            ->delete(route('admin.invitations.destroy', $invitation))
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('success');

        $this->assertModelMissing($invitation);
        $this->assertModelMissing($guest);
        $this->assertModelMissing($export);
        // El archivo que ya se había generado tampoco queda dando vueltas
        Storage::disk('local')->assertMissing("exports/{$invitation->id}/invitados.xlsx");
    }

    public function test_a_client_cannot_delete_an_invitation_not_even_its_own(): void
    {
        $client = User::factory()->create();
        $invitation = $this->createInvitation(['user_id' => $client->id]);

        $this->actingAs($client)
            ->delete(route('admin.invitations.destroy', $invitation))
            ->assertForbidden();

        $this->assertModelExists($invitation);
    }

    public function test_the_visual_reference_page_is_gone(): void
    {
        $this->actingAs($this->admin)->get('/admin/sistema-visual')->assertNotFound();
        $this->assertFalse(Route::has('admin.design-system'));
    }

    private function eventType(string $slug, string $name, string $kind = 'invitation'): EventType
    {
        return EventType::firstOrCreate(['slug' => $slug], ['name' => $name, 'kind' => $kind]);
    }
}
