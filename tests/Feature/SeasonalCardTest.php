<?php

namespace Tests\Feature;

use App\Models\EventType;
use App\Models\GuestContribution;
use App\Models\Invitation;
use App\Models\User;
use App\Modules\Card\ReplyModule;
use App\Services\InvitationModuleService;
use App\Support\InvitationTemplates;
use Database\Seeders\ShowcaseInvitationsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Js;
use Illuminate\Support\Str;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Tarjetas de temporada (primera: Día del Amor): se arman con su perfil, no con el de una
 * invitación, y quien la recibe puede responder sin que la respuesta se publique.
 */
class SeasonalCardTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    private const THROTTLE_MESSAGE = 'Demasiados intentos. Espera un momento e inténtalo de nuevo.';

    public function test_the_card_shows_its_letter_and_not_the_parts_of_an_event(): void
    {
        $card = $this->createCard(['slug' => 'para-ana']);

        $this->withoutVite()
            ->get(route('invitation.show', 'para-ana'))
            ->assertOk()
            ->assertSee('inv-page inv-amor', false)
            ->assertSee('id="dedicatoria"', false)
            ->assertSee('id="juntos-desde"', false)
            ->assertSee('id="respuesta"', false)
            ->assertSee('x-data="flowerReply(', false)
            ->assertDontSee('id="rsvp"', false)
            ->assertDontSee('id="itinerario"', false)
            ->assertDontSee('id="ubicacion"', false);
    }

    public function test_the_story_mode_keeps_every_scene_readable_in_the_html(): void
    {
        $this->createCard(['slug' => 'para-ana']);

        $this->withoutVite()
            ->get(route('invitation.show', 'para-ana'))
            ->assertOk()
            // Controles del modo historia: solo aparecen con JavaScript
            ->assertSee('data-story x-data="invitationStory(', false)
            ->assertSee('Ver todo')
            // La carta está entera debajo del sello, y el sello necesita JavaScript
            ->assertSee('x-data="holdToOpen(', false)
            ->assertSee('class="inv-letter-gate__cover" data-needs-js', false)
            ->assertSee('Gracias por las mañanas de café')
            ->assertSee('Mantén presionado el sello para abrir la carta')
            // El sobre con el sello de lacre (la carta queda debajo, oculta solo con JavaScript)
            ->assertSee('class="inv-envelope"', false)
            // El tiempo juntos ya viene calculado debajo de la margarita, con primaveras y latidos
            ->assertSee('x-data="daisyGate(', false)
            ->assertSee('Ver la respuesta sin deshojar')
            ->assertSee('primaveras juntos')
            ->assertSee('millones de latidos')
            // Respuesta con flor y su significado, y el deseo del final
            ->assertSee('Me haces feliz')
            ->assertSee('id="deseo"', false)
            ->assertSeeInOrder(['id="inicio"', 'id="dedicatoria"', 'id="juntos-desde"', 'id="galeria"', 'id="respuesta"'], false);
    }

    public function test_the_editor_preview_does_not_turn_the_card_into_a_story(): void
    {
        // Se compara con la misma codificación que usa @js en la vista
        $public = view('invitations.partials.story.chrome')->render();
        $preview = view('invitations.partials.story.chrome', ['isPreview' => true])->render();

        $this->assertStringContainsString('invitationStory('.Js::from(['enabled' => true]).')', $public);
        $this->assertStringContainsString('invitationStory('.Js::from(['enabled' => false]).')', $preview);
    }

    public function test_the_card_is_shared_as_a_letter_from_one_person_to_another(): void
    {
        $this->createCard(['slug' => 'para-ana']);

        $html = $this->withoutVite()->get(route('invitation.show', 'para-ana'))->assertOk()->getContent();

        $this->assertStringContainsString('<meta property="og:title" content="Para Ana, de Luis">', $html);
        $this->assertMatchesRegularExpression('/<meta property="og:description" content="[^"]+ · Abre la carta">/', $html);
    }

    public function test_a_reply_reaches_the_client_panel_and_is_not_published(): void
    {
        $client = User::factory()->create(['is_admin' => false]);
        $card = $this->createCard(['user_id' => $client->id]);

        $this->postJson(route('invitation.reply', $card->slug), ['content_text' => "  Te quiero mucho.\nGracias por todo  "])
            ->assertOk()
            ->assertJson(['success' => true]);

        $reply = GuestContribution::where('invitation_id', $card->id)->sole();
        $this->assertSame(ReplyModule::CONTRIBUTION_TYPE, $reply->type);
        $this->assertSame("Te quiero mucho.\nGracias por todo", $reply->content_text);

        // La carta pública no la muestra
        $this->withoutVite()->get(route('invitation.show', $card->slug))->assertDontSee('Gracias por todo');

        // El cliente la lee completa y la puede ocultar sin borrarla
        $this->actingAs($client)
            ->get(route('client.invitation.show', $card))
            ->assertOk()
            ->assertSee('Respuestas a tu tarjeta')
            ->assertSee('Gracias por todo');

        $this->actingAs($client)
            ->patch(route('client.contributions.update', [$card, $reply]), ['moderation_status' => GuestContribution::HIDDEN])
            ->assertRedirect();

        $this->assertDatabaseHas('guest_contributions', ['id' => $reply->id, 'moderation_status' => GuestContribution::HIDDEN]);
    }

    public function test_a_reply_is_validated_and_stays_in_its_own_card(): void
    {
        $card = $this->createCard();
        $other = $this->createCard();

        $this->postJson(route('invitation.reply', $card->slug), ['content_text' => ''])->assertUnprocessable();
        $this->postJson(route('invitation.reply', $card->slug), ['content_text' => str_repeat('a', 501)])->assertUnprocessable();
        // Solo las flores que ofrece la plantilla
        $this->postJson(route('invitation.reply', $card->slug), ['reaction' => 'cactus'])->assertUnprocessable();

        $this->postJson(route('invitation.reply', $card->slug), ['content_text' => 'Solo para esta carta'])->assertOk();

        $this->assertDatabaseCount('guest_contributions', 1);
        $this->assertSame(0, GuestContribution::where('invitation_id', $other->id)->count());
    }

    public function test_replies_are_rejected_when_the_module_is_off_or_the_page_is_an_invitation(): void
    {
        $modules = ShowcaseInvitationsSeeder::data('tarjeta-ana-luis')['modules'];
        $modules['config']['modulos']['respuesta'] = false;
        $silent = $this->createCard([], $modules);

        $this->postJson(route('invitation.reply', $silent->slug), ['content_text' => 'Hola'])->assertNotFound();

        $invitation = $this->createInvitation();
        $this->postJson(route('invitation.reply', $invitation->slug), ['content_text' => 'Hola'])->assertNotFound();

        $this->postJson(route('invitation.reply', 'no-existe'), ['content_text' => 'Hola'])->assertNotFound();

        $this->assertDatabaseCount('guest_contributions', 0);
    }

    public function test_replies_have_a_per_minute_limit(): void
    {
        config(['optimizations.rate_limits.replies' => 1]);
        $card = $this->createCard();

        $this->postJson(route('invitation.reply', $card->slug), ['content_text' => 'Primera'])->assertOk();

        $this->postJson(route('invitation.reply', $card->slug), ['content_text' => 'Segunda'])
            ->assertStatus(429)
            ->assertJson(['success' => false, 'message' => self::THROTTLE_MESSAGE]);
    }

    public function test_the_card_campaign_page_shows_the_season_price_and_links_its_demo(): void
    {
        config(['bida.seasons.amor.ends_at' => '2026-09-21 23:59:59']);
        $this->travelTo(Carbon::parse('2026-09-19 10:00', 'America/La_Paz'));
        $this->seed(ShowcaseInvitationsSeeder::class);
        $landing = config('bida.landings.tarjetas-dia-del-amor');

        $this->withoutVite()
            ->get(route('landing', 'tarjetas-dia-del-amor'))
            ->assertOk()
            ->assertSee(e($landing['heading']), false)
            // Las muestras son las de la temporada
            ->assertSee(route('invitation.demo', config('bida.seasons.amor.templates')[0]), false)
            ->assertSeeInOrder(['US$ 14', '11', 'USD'])
            ->assertSee('La quiero por US$ 11');

        // Pasada la temporada ya no se vende: queda el contacto
        $this->travelTo(Carbon::parse('2026-09-22 08:00', 'America/La_Paz'));

        $this->withoutVite()
            ->get(route('landing', 'tarjetas-dia-del-amor'))
            ->assertOk()
            ->assertDontSee('La quiero por US$ 11')
            ->assertSee('La temporada terminó');
    }

    public function test_a_reply_flower_has_its_meaning_for_the_client(): void
    {
        $client = User::factory()->create(['is_admin' => false]);
        $card = $this->createCard(['user_id' => $client->id]);

        $this->postJson(route('invitation.reply', $card->slug), ['reaction' => 'tulipan'])->assertOk();

        $this->actingAs($client)
            ->get(route('client.invitation.show', $card))
            ->assertOk()
            ->assertSee('Te respondió con un tulipán')
            ->assertSee('Amor sincero');
    }

    public function test_the_editor_offers_cards_with_their_own_profile(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $html = $this->actingAs($admin)
            ->get(route('admin.invitations.create'))
            ->assertOk()
            ->assertSee('¿Qué vas a crear?')
            ->getContent();

        $this->assertStringContainsString(e(InvitationTemplates::TARJETA_AMOR), $html);
        // Los perfiles viajan al editor (JSON escapado por @js): la tarjeta llega con su tipo
        $this->assertStringContainsString('\u0022code\u0022:\u0022amor\u0022', $html);
        $this->assertStringContainsString('\u0022kind\u0022:\u0022card\u0022', $html);
    }

    public function test_the_admin_dashboard_filters_cards_and_invitations(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->createCard(['title' => 'Carta para Ana']);
        $this->createInvitation(['title' => 'XV de Sofía']);

        $this->actingAs($admin)->get(route('admin.dashboard', ['tipo' => 'card']))
            ->assertOk()
            ->assertSee('Carta para Ana')
            ->assertDontSee('XV de Sofía');

        $this->actingAs($admin)->get(route('admin.dashboard', ['tipo' => 'invitation']))
            ->assertOk()
            ->assertSee('XV de Sofía')
            ->assertDontSee('Carta para Ana');

        $this->actingAs($admin)->get(route('admin.dashboard', ['tipo' => 'otra-cosa']))
            ->assertOk()
            ->assertSee('Carta para Ana')
            ->assertSee('XV de Sofía');
    }

    private function createCard(array $attributes = [], ?array $modules = null): Invitation
    {
        $eventType = EventType::firstOrCreate(
            ['slug' => 'dia-del-amor'],
            ['name' => 'Día del Amor', 'code' => 'amor', 'kind' => 'card', 'season' => 'amor']
        );

        $card = $this->createInvitation(array_merge([
            'event_type_id' => $eventType->id,
            'slug' => 'carta-'.Str::lower(Str::random(6)),
            'template' => InvitationTemplates::TARJETA_AMOR,
            'title' => 'Carta de Luis para Ana',
        ], $attributes));

        app(InvitationModuleService::class)->syncAllModules(
            $card,
            $modules ?? ShowcaseInvitationsSeeder::data('tarjeta-ana-luis')['modules']
        );

        return $card;
    }
}
