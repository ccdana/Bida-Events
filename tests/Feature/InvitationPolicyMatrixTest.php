<?php

namespace Tests\Feature;

use App\Models\GuestContribution;
use App\Models\Invitation;
use App\Models\InvitationExport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Punto 29: cada método de InvitationPolicy, probado en la regla y en su ruta.
 * El caso que importa siempre es el mismo: un cliente frente a la invitación de otro.
 */
class InvitationPolicyMatrixTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    private User $owner;

    private User $otherClient;

    private User $admin;

    private Invitation $invitation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->otherClient = User::factory()->create();
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->invitation = $this->createInvitation(['user_id' => $this->owner->id]);
    }

    public function test_view(): void
    {
        $this->assertTrue($this->owner->can('view', $this->invitation));
        $this->assertFalse($this->otherClient->can('view', $this->invitation));
        $this->assertTrue($this->admin->can('view', $this->invitation));

        $this->actingAs($this->otherClient)->get(route('client.invitation.show', $this->invitation))->assertForbidden();
        $this->actingAs($this->owner)->get(route('client.invitation.show', $this->invitation))->assertOk();
    }

    public function test_export(): void
    {
        $this->assertTrue($this->owner->can('export', $this->invitation));
        $this->assertFalse($this->otherClient->can('export', $this->invitation));

        foreach (array_keys(InvitationExport::TYPES) as $type) {
            $this->actingAs($this->otherClient)
                ->post(route('client.export.store', [$this->invitation, $type]))
                ->assertForbidden();
        }

        // Tampoco puede consultar ni descargar un archivo que pidió el dueño
        $export = InvitationExport::create([
            'invitation_id' => $this->invitation->id,
            'user_id' => $this->owner->id,
            'type' => 'guests-excel',
            'status' => InvitationExport::READY,
            'path' => 'exports/prueba.xlsx',
        ]);

        $this->actingAs($this->otherClient)->get(route('client.export.status', $export))->assertForbidden();
        $this->actingAs($this->otherClient)->get(route('client.export.download', $export))->assertForbidden();
    }

    public function test_update(): void
    {
        // Editar es del administrador, ni siquiera del dueño
        $this->assertFalse($this->owner->can('update', $this->invitation));
        $this->assertFalse($this->otherClient->can('update', $this->invitation));
        $this->assertTrue($this->admin->can('update', $this->invitation));

        $this->actingAs($this->owner)->get(route('admin.invitations.edit', $this->invitation))->assertForbidden();
        $this->actingAs($this->otherClient)->put(route('admin.invitations.update', $this->invitation), ['title' => 'Ajena'])->assertForbidden();

        $this->assertSame('XV Años de prueba', $this->invitation->fresh()->title);
    }

    public function test_manage_guests(): void
    {
        $this->assertFalse($this->owner->can('manageGuests', $this->invitation));
        $this->assertFalse($this->otherClient->can('manageGuests', $this->invitation));
        $this->assertTrue($this->admin->can('manageGuests', $this->invitation));

        $guest = $this->invitation->guests()->create(['name' => 'Familia Mamani', 'passes_allocated' => 2]);
        $this->actingAs($this->otherClient);

        $this->get(route('admin.guests.index', $this->invitation))->assertForbidden();
        $this->post(route('admin.guests.store', $this->invitation), ['name' => 'Colado', 'passes_allocated' => 9])->assertForbidden();
        $this->put(route('admin.guests.update', [$this->invitation, $guest]), ['name' => 'Cambiado', 'passes_allocated' => 9])->assertForbidden();
        $this->post(route('admin.guests.token', [$this->invitation, $guest]))->assertForbidden();
        $this->delete(route('admin.guests.destroy', [$this->invitation, $guest]))->assertForbidden();

        $this->assertSame('Familia Mamani', $guest->fresh()->name);
        $this->assertSame(1, $this->invitation->guests()->count());
    }

    public function test_moderate_contributions(): void
    {
        $this->assertTrue($this->owner->can('moderateContributions', $this->invitation));
        $this->assertFalse($this->otherClient->can('moderateContributions', $this->invitation));

        $song = GuestContribution::create([
            'invitation_id' => $this->invitation->id,
            'type' => 'song_request',
            'content_text' => 'Vivir mi vida',
        ]);

        $this->actingAs($this->otherClient)
            ->patch(route('client.contributions.update', [$this->invitation, $song]), ['moderation_status' => GuestContribution::HIDDEN])
            ->assertForbidden();

        $this->assertSame(GuestContribution::VISIBLE, $song->fresh()->moderation_status);
    }

    public function test_guests_without_session_are_sent_to_login(): void
    {
        $this->get(route('client.invitation.show', $this->invitation))->assertRedirect(route('login'));
        $this->get(route('admin.invitations.edit', $this->invitation))->assertRedirect(route('login'));
    }
}
