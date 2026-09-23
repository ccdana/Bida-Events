<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\User;
use App\Modules\Card\ReplyModule;
use App\Support\InvitationTemplates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * El panel del cliente: sus eventos separados en secciones, el buscador, el enlace a su página
 * y la lista de invitados que él mismo puede armar.
 */
class ClientPortalTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
    }

    public function test_the_panel_separates_invitations_from_cards_and_offers_the_public_link(): void
    {
        $invitation = $this->createInvitation(['user_id' => $this->owner->id, 'slug' => 'xv-lucia', 'title' => 'XV de Lucía']);
        $card = $this->createInvitation([
            'user_id' => $this->owner->id,
            'slug' => 'tarjeta-ana',
            'title' => 'Tarjeta para Ana',
            'template' => InvitationTemplates::TARJETA_AMOR,
        ]);
        // Sin publicar: en vez del enlace, el cliente ve por qué todavía no se puede abrir
        $draft = $this->createInvitation(['user_id' => $this->owner->id, 'slug' => 'xv-borrador', 'title' => 'XV sin publicar', 'status' => 'inactive']);

        $this->actingAs($this->owner)
            ->withoutVite()
            ->get(route('client.dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['Mis invitaciones', 'XV de Lucía', 'Mis tarjetas', 'Tarjeta para Ana'], false)
            ->assertSee(route('invitation.show', $invitation->slug), false)
            ->assertSee(route('invitation.show', $card->slug), false)
            ->assertDontSee(route('invitation.show', $draft->slug), false)
            ->assertSee('Todavía sin publicar');
    }

    public function test_the_search_box_leaves_only_the_matching_events(): void
    {
        $this->createInvitation(['user_id' => $this->owner->id, 'title' => 'Boda de Marta y José']);
        $this->createInvitation(['user_id' => $this->owner->id, 'title' => 'XV de Lucía']);

        $this->actingAs($this->owner)
            ->withoutVite()
            ->get(route('client.dashboard', ['q' => 'boda']))
            ->assertOk()
            ->assertSee('Boda de Marta y José')
            ->assertDontSee('XV de Lucía');

        $this->actingAs($this->owner)
            ->withoutVite()
            ->get(route('client.dashboard', ['q' => 'cumpleaños']))
            ->assertOk()
            ->assertSee('Ningún evento se llama así');
    }

    public function test_the_owner_adds_a_guest_and_gets_the_personal_link(): void
    {
        $invitation = $this->createInvitation(['user_id' => $this->owner->id, 'slug' => 'xv-lista']);

        $this->actingAs($this->owner)
            ->from(route('client.invitation.show', $invitation))
            ->post(route('client.guests.store', $invitation), [
                'name' => 'Familia Quispe',
                'phone' => '71234567',
                'passes_allocated' => 3,
            ])
            ->assertRedirect(route('client.invitation.show', $invitation));

        $guest = Guest::where('invitation_id', $invitation->id)->firstOrFail();
        $this->assertSame(3, $guest->passes_allocated);
        $this->assertSame('pending', $guest->status);
        $this->assertNotEmpty($guest->qr_code_token);
        $this->assertSame(
            route('invitation.guest', [$invitation->slug, $guest->qr_code_token]),
            session('guest')['link']
        );

        // Y en su página ve al invitado con su enlace listo para enviar
        $this->withoutVite()
            ->get(route('client.invitation.show', $invitation))
            ->assertOk()
            ->assertSee('Familia Quispe')
            ->assertSee(route('invitation.guest', [$invitation->slug, $guest->qr_code_token]), false);
    }

    public function test_a_guest_who_already_answered_cannot_be_removed(): void
    {
        $invitation = $this->createInvitation(['user_id' => $this->owner->id]);
        $pending = $invitation->guests()->create(['name' => 'Familia Rojas', 'passes_allocated' => 2]);
        $confirmed = $invitation->guests()->create(['name' => 'Ana Quispe', 'passes_allocated' => 2, 'passes_confirmed' => 2, 'status' => 'confirmed']);

        $this->actingAs($this->owner)
            ->delete(route('client.guests.destroy', [$invitation, $pending]))
            ->assertRedirect();
        $this->assertModelMissing($pending);

        $this->actingAs($this->owner)
            ->delete(route('client.guests.destroy', [$invitation, $confirmed]))
            ->assertForbidden();
        $this->assertModelExists($confirmed);
    }

    public function test_only_the_owner_manages_the_list_and_cards_do_not_have_one(): void
    {
        $invitation = $this->createInvitation(['user_id' => $this->owner->id]);
        $card = $this->createInvitation(['user_id' => $this->owner->id, 'template' => InvitationTemplates::TARJETA_AMOR]);
        $payload = ['name' => 'Alguien', 'passes_allocated' => 1];

        // Otro cliente no toca la lista ajena
        $this->actingAs(User::factory()->create())
            ->post(route('client.guests.store', $invitation), $payload)
            ->assertForbidden();

        // Una tarjeta se manda a una sola persona: no tiene lista de invitados
        $this->actingAs($this->owner)
            ->post(route('client.guests.store', $card), $payload)
            ->assertForbidden();

        $this->assertDatabaseCount('guests', 0);

        // Y el invitado tiene que ser de esa invitación (scopeBindings)
        $otherInvitation = $this->createInvitation(['user_id' => $this->owner->id]);
        $guest = $otherInvitation->guests()->create(['name' => 'Familia Rojas', 'passes_allocated' => 1]);

        $this->actingAs($this->owner)
            ->delete(route('client.guests.destroy', [$invitation, $guest]))
            ->assertNotFound();
    }

    public function test_the_card_owner_reads_the_answers_and_downloads_only_its_pdf(): void
    {
        $card = $this->createInvitation([
            'user_id' => $this->owner->id,
            'template' => InvitationTemplates::TARJETA_AMOR,
            'title' => 'Tarjeta para Ana',
        ]);
        $card->contributions()->create([
            'type' => ReplyModule::CONTRIBUTION_TYPE,
            'content_text' => 'Gracias por la tarjeta, me encantó.',
            'reaction' => 'rosa',
            'moderation_status' => 'visible',
        ]);

        $this->actingAs($this->owner)
            ->withoutVite()
            ->get(route('client.invitation.show', $card))
            ->assertOk()
            ->assertSee('Respuestas a tu tarjeta')
            ->assertSee('Gracias por la tarjeta, me encantó.')
            ->assertSee('una rosa')
            // Ni lista de invitados ni reportes de invitados: no aplican a una tarjeta
            ->assertDontSee('Mis invitados')
            ->assertSee(route('client.export.store', [$card, 'invitation-pdf']), false)
            ->assertDontSee(route('client.export.store', [$card, 'guests-excel']), false);
    }
}
