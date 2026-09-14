<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\PollVote;
use Database\Seeders\ShowcaseInvitationsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowcaseInvitationsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_recreates_every_showcase_invitation_with_its_guest_activity(): void
    {
        // Dos veces: el seeder no debe duplicar nada
        $this->seed(ShowcaseInvitationsSeeder::class);
        $this->seed(ShowcaseInvitationsSeeder::class);

        $this->assertSame(ShowcaseInvitationsSeeder::SLUGS, Invitation::orderBy('id')->pluck('slug')->all());

        foreach (ShowcaseInvitationsSeeder::SLUGS as $slug) {
            $data = ShowcaseInvitationsSeeder::data($slug);
            $invitation = Invitation::where('slug', $slug)->firstOrFail();

            $this->assertSame(count($data['guests']), $invitation->guests()->count());
            $this->assertSame(count($data['contributions']), $invitation->contributions()->count());
            $this->assertSame(count($data['poll_votes']), PollVote::where('invitation_id', $invitation->id)->whereNotNull('invitation_poll_id')->count());
            $this->assertStringContainsString('res.cloudinary.com', $data['modules']['bienvenida']['imagen_hero']);

            $this->withoutVite()
                ->get(route('invitation.show', $slug))
                ->assertOk()
                ->assertSee('res.cloudinary.com', false);
        }
    }
}
