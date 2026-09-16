<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

class PublicInteractionsTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public function test_public_page_renders_responsive_cloudinary_images_with_a_legacy_template_name(): void
    {
        $invitation = $this->createInvitation(['template' => 'pages.invitations.templates.xv-premium']);
        app(\App\Services\InvitationModuleService::class)->syncAllModules($invitation, \Database\Seeders\XvSofiaModuleData::all());

        $this->withoutVite()
            ->get(route('invitation.show', $invitation->slug))
            ->assertOk()
            // Hero: atributo srcset en HTML plano
            ->assertSee('upload/f_auto,q_auto,c_limit,w_1920/v1690000000/xv-sofia/hero.jpg 1920w', false)
            // Galería: las URLs llegan a Alpine como JSON escapado por @js
            ->assertSee('f_auto,q_auto,c_limit,w_1200', false)
            ->assertSee('galleryStack(JSON.parse(', false);
    }

    public function test_rsvp_confirmation_caps_passes_to_the_allocation(): void
    {
        $invitation = $this->createInvitation();
        $guest = $invitation->guests()->create(['name' => 'Familia Rojas', 'passes_allocated' => 3]);

        $this->postJson(
            route('invitation.rsvp', ['slug' => $invitation->slug, 'token' => $guest->qr_code_token]),
            ['status' => 'confirmed', 'passes_confirmed' => 10]
        )
            ->assertOk()
            ->assertJson(['success' => true, 'status' => 'confirmed', 'passes_confirmed' => 3]);

        $this->assertSame('confirmed', $guest->fresh()->status);
    }

    public function test_rsvp_is_rejected_for_expired_invitations(): void
    {
        $invitation = $this->createInvitation(['expires_at' => now()->subDay()]);
        $guest = $invitation->guests()->create(['name' => 'Familia Rojas']);

        $this->postJson(
            route('invitation.rsvp', ['slug' => $invitation->slug, 'token' => $guest->qr_code_token]),
            ['status' => 'declined']
        )->assertNotFound();
    }

    public function test_new_song_refreshes_the_cached_playlist(): void
    {
        $invitation = $this->createInvitation();
        Cache::put("invitation.{$invitation->id}.playlist", ['cacheada'], 300);

        $this->postJson(route('invitation.playlist', $invitation->slug), ['content_text' => 'Bailando — Enrique Iglesias'])
            ->assertOk();

        $this->assertDatabaseHas('guest_contributions', ['invitation_id' => $invitation->id, 'type' => 'song_request']);
        $this->assertFalse(Cache::has("invitation.{$invitation->id}.playlist"));
    }

    public function test_photo_upload_is_stored_and_refreshes_the_cached_fotomural(): void
    {
        config(['cloudinary.cloud_url' => null]);
        Storage::fake('public');
        $invitation = $this->createInvitation();
        Cache::put("invitation.{$invitation->id}.fotomural", ['cacheada'], 300);

        $this->postJson(route('invitation.fotomural', $invitation->slug), [
            'photo' => UploadedFile::fake()->image('foto.jpg', 800, 600),
        ])->assertOk();

        $this->assertDatabaseHas('guest_contributions', ['invitation_id' => $invitation->id, 'type' => 'live_photo']);
        $this->assertFalse(Cache::has("invitation.{$invitation->id}.fotomural"));
    }

    public function test_poll_votes_refresh_cached_results_and_are_throttled(): void
    {
        config(['optimizations.rate_limits.votes' => 2]);
        $invitation = $this->createInvitation();
        app(\App\Services\InvitationModuleService::class)->syncAllModules($invitation, \Database\Seeders\XvSofiaModuleData::all());
        Cache::put("invitation.{$invitation->id}.polls", ['cacheada'], 300);

        $vote = fn (string $pollId) => $this->postJson(
            route('invitation.poll.vote', ['slug' => $invitation->slug, 'pollId' => $pollId]),
            ['option_index' => 0, 'guest_token' => 'votante-1']
        );

        $vote('color-vestido')->assertOk();
        $this->assertFalse(Cache::has("invitation.{$invitation->id}.polls"));

        $vote('nivel-fiesta')->assertOk();
        $vote('caida-pista')
            ->assertStatus(429)
            ->assertJson(['success' => false]);
    }

    public function test_a_vote_needs_a_real_poll_a_real_option_and_solo_cuenta_una_vez(): void
    {
        config(['optimizations.rate_limits.votes' => 30]);
        $invitation = $this->createInvitation();
        app(\App\Services\InvitationModuleService::class)->syncAllModules($invitation, \Database\Seeders\XvSofiaModuleData::all());

        $vote = fn (string $pollId, int $option) => $this->postJson(
            route('invitation.poll.vote', ['slug' => $invitation->slug, 'pollId' => $pollId]),
            ['option_index' => $option, 'guest_token' => 'votante-1']
        );

        // Una encuesta que no es de esta invitación no acepta votos
        $vote('encuesta-inventada', 0)->assertNotFound();

        // Tampoco una opción que no existe en la encuesta
        $vote('color-vestido', 99)->assertStatus(422);

        // El voto válido cuenta una sola vez por votante
        $vote('color-vestido', 1)->assertOk();
        $vote('color-vestido', 2)
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertSame(1, \App\Models\PollVote::where('invitation_id', $invitation->id)->count());
    }
}
