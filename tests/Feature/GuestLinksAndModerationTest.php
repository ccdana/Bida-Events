<?php

namespace Tests\Feature;

use App\Models\GuestContribution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

class GuestLinksAndModerationTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public function test_admin_can_regenerate_the_personal_link_of_a_guest(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $invitation = $this->createInvitation();
        $guest = $invitation->guests()->create(['name' => 'Familia Pérez', 'passes_allocated' => 2]);
        $oldToken = $guest->qr_code_token;

        $this->actingAs($admin)
            ->post(route('admin.guests.token', [$invitation, $guest]))
            ->assertRedirect();

        $guest->refresh();
        $this->assertNotSame($oldToken, $guest->qr_code_token);

        // El enlace viejo deja de servir y el nuevo funciona
        $this->withoutVite()->get(route('invitation.guest', [$invitation->slug, $oldToken]))->assertNotFound();
        $this->withoutVite()->get(route('invitation.guest', [$invitation->slug, $guest->qr_code_token]))->assertOk();
    }

    public function test_the_client_can_hide_a_photo_without_deleting_it(): void
    {
        $client = User::factory()->create(['is_admin' => false]);
        $invitation = $this->createInvitation(['user_id' => $client->id]);
        $photo = GuestContribution::create([
            'invitation_id' => $invitation->id,
            'type' => 'live_photo',
            'file_path' => 'https://res.cloudinary.com/demo/image/upload/v1/foto.jpg',
            'created_at' => now(),
        ]);

        $this->actingAs($client)
            ->patch(route('client.contributions.update', [$invitation, $photo]), ['moderation_status' => 'hidden'])
            ->assertRedirect();

        // Sigue guardada, pero la invitación ya no la muestra
        $this->assertDatabaseHas('guest_contributions', ['id' => $photo->id, 'moderation_status' => 'hidden']);
        $this->getJson(route('invitation.fotomural.list', $invitation->slug))
            ->assertOk()
            ->assertJsonCount(0, 'photos');

        // Y se puede volver a mostrar
        $this->actingAs($client)
            ->patch(route('client.contributions.update', [$invitation, $photo]), ['moderation_status' => 'visible']);

        $this->getJson(route('invitation.fotomural.list', $invitation->slug))
            ->assertOk()
            ->assertJsonCount(1, 'photos');
    }

    public function test_a_client_cannot_moderate_another_invitation(): void
    {
        $client = User::factory()->create(['is_admin' => false]);
        $otherInvitation = $this->createInvitation(['user_id' => User::factory()->create()->id]);
        $photo = GuestContribution::create([
            'invitation_id' => $otherInvitation->id,
            'type' => 'live_photo',
            'file_path' => 'https://res.cloudinary.com/demo/image/upload/v1/foto.jpg',
            'created_at' => now(),
        ]);

        $this->actingAs($client)
            ->patch(route('client.contributions.update', [$otherInvitation, $photo]), ['moderation_status' => 'hidden'])
            ->assertForbidden();
    }

    public function test_hidden_songs_do_not_reach_the_public_playlist(): void
    {
        $invitation = $this->createInvitation();
        GuestContribution::create([
            'invitation_id' => $invitation->id,
            'type' => 'song_request',
            'content_text' => 'Canción visible',
            'created_at' => now(),
        ]);
        GuestContribution::create([
            'invitation_id' => $invitation->id,
            'type' => 'song_request',
            'content_text' => 'Canción oculta',
            'moderation_status' => 'hidden',
            'created_at' => now(),
        ]);

        $this->getJson(route('invitation.playlist.list', $invitation->slug))
            ->assertOk()
            ->assertJsonCount(1, 'songs')
            ->assertJsonPath('songs.0.text', 'Canción visible');
    }
}
