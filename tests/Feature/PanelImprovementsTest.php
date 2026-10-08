<?php

namespace Tests\Feature;

use App\Models\GuestContribution;
use App\Models\PollVote;
use App\Models\User;
use App\Services\InvitationModuleService;
use App\Support\InvitationTemplates;
use Database\Seeders\BodaJardinDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Sin página de error 403 en los paneles, resultados de encuestas para el cliente, ocultar sin recargar
 * y hashtags largos que se parten por palabras.
 */
class PanelImprovementsTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public function test_a_403_in_the_panels_goes_back_with_a_notice_instead_of_the_error_page(): void
    {
        $client = User::factory()->create(['is_admin' => false]);

        $this->actingAs($client)
            ->get(route('admin.settings'))
            ->assertRedirect(route('client.dashboard'))
            ->assertSessionHas('access_notice', 'Acceso restringido al panel administrativo.');

        // Con el aviso a la vista en el panel de destino
        $this->actingAs($client)->withoutVite()
            ->withSession(['access_notice' => 'Acceso restringido al panel administrativo.'])
            ->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('Acceso restringido al panel administrativo.');
    }

    public function test_it_returns_to_the_page_the_person_came_from(): void
    {
        $client = User::factory()->create(['is_admin' => false]);

        $this->actingAs($client)
            ->from(route('client.account'))
            ->get(route('admin.settings'))
            ->assertRedirect(route('client.account'));
    }

    public function test_the_framework_english_message_is_replaced_by_a_spanish_one(): void
    {
        $client = User::factory()->create(['is_admin' => false]);
        $other = $this->createInvitation(['user_id' => User::factory()->create()->id]);

        $this->actingAs($client)
            ->get(route('client.invitation.show', $other))
            ->assertRedirect()
            ->assertSessionHas('access_notice', 'No tienes acceso a esa sección.');
    }

    public function test_fetch_requests_still_get_a_403_so_the_page_scripts_can_react(): void
    {
        $client = User::factory()->create(['is_admin' => false]);

        $this->actingAs($client)->getJson(route('admin.settings'))->assertForbidden();
    }

    public function test_a_visitor_without_session_is_sent_to_the_login(): void
    {
        $this->get(route('admin.settings'))->assertRedirect(route('login'));
    }

    public function test_the_client_sees_the_results_of_the_polls(): void
    {
        $client = User::factory()->create(['is_admin' => false]);
        $invitation = $this->createInvitation(['user_id' => $client->id]);

        $poll = $invitation->polls()->create(['poll_key' => 'musica', 'question' => '¿Qué música prefieres?', 'type' => 'single', 'sort_order' => 0]);
        foreach (['Cumbia', 'Rock', 'Salsa'] as $index => $label) {
            $poll->options()->create(['label' => $label, 'value' => $label, 'sort_order' => $index]);
        }
        foreach ([0, 0, 0, 1] as $i => $option) {
            PollVote::create(['invitation_id' => $invitation->id, 'invitation_poll_id' => $poll->id, 'option_index' => $option, 'voter_key' => "v{$i}", 'created_at' => now()]);
        }

        $this->actingAs($client)->withoutVite()
            ->get(route('client.invitation.show', $invitation))
            ->assertOk()
            ->assertSee('Resultados de las encuestas')
            ->assertSee('¿Qué música prefieres?')
            ->assertSee('4 votos')
            ->assertSeeInOrder(['Cumbia', '3', '75%', 'Rock', '1', '25%', 'Salsa', '0', '0%']);
    }

    public function test_the_polls_section_is_hidden_when_there_are_no_polls(): void
    {
        $client = User::factory()->create(['is_admin' => false]);
        $invitation = $this->createInvitation(['user_id' => $client->id]);

        $this->actingAs($client)->withoutVite()
            ->get(route('client.invitation.show', $invitation))
            ->assertOk()
            ->assertDontSee('Resultados de las encuestas');
    }

    public function test_hiding_a_photo_answers_json_so_the_page_does_not_reload(): void
    {
        $client = User::factory()->create(['is_admin' => false]);
        $invitation = $this->createInvitation(['user_id' => $client->id]);
        $photo = $this->photo($invitation);

        $this->actingAs($client)
            ->patchJson(route('client.contributions.update', [$invitation, $photo]), ['moderation_status' => 'hidden'])
            ->assertOk()
            ->assertJson(['success' => true, 'hidden' => true]);

        $this->assertDatabaseHas('guest_contributions', ['id' => $photo->id, 'moderation_status' => 'hidden']);

        $this->actingAs($client)
            ->patchJson(route('client.contributions.update', [$invitation, $photo]), ['moderation_status' => 'visible'])
            ->assertJson(['success' => true, 'hidden' => false]);

        // Sin JavaScript el formulario sigue funcionando como siempre
        $this->actingAs($client)
            ->patch(route('client.contributions.update', [$invitation, $photo]), ['moderation_status' => 'hidden'])
            ->assertRedirect();
    }

    public function test_the_photos_panel_is_an_instagram_style_grid_with_in_place_moderation(): void
    {
        $client = User::factory()->create(['is_admin' => false]);
        $invitation = $this->createInvitation(['user_id' => $client->id]);
        $this->photo($invitation);

        $this->actingAs($client)->withoutVite()
            ->get(route('client.invitation.show', $invitation))
            ->assertOk()
            ->assertSee('grid-cols-3', false)
            ->assertSee('aspect-square', false)
            ->assertSee('x-data="moderation(', false);
    }

    public function test_a_long_hashtag_can_break_between_its_words(): void
    {
        $invitation = $this->createInvitation(['slug' => 'hashtag-largo', 'template' => InvitationTemplates::BODA_JARDIN]);
        $modules = BodaJardinDemoSeeder::modules();
        $modules['hashtag'] = ['hashtag' => '#MariaFernandaYJuanCarlosSeCasanEnCochabamba2026', 'plataforma' => 'instagram'];
        app(InvitationModuleService::class)->syncAllModules($invitation, $modules);

        $this->withoutVite()->get(route('invitation.show', $invitation->slug))
            ->assertOk()
            ->assertSee('#Maria<wbr>Fernanda<wbr>Y<wbr>Juan<wbr>Carlos<wbr>Se<wbr>Casan<wbr>En<wbr>Cochabamba2026', false);
    }

    private function photo($invitation): GuestContribution
    {
        return GuestContribution::create([
            'invitation_id' => $invitation->id,
            'type' => 'live_photo',
            'file_path' => 'https://res.cloudinary.com/demo/image/upload/v1/foto.jpg',
            'created_at' => now(),
        ]);
    }
}
