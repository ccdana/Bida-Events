<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * El inicio del cliente y del revendedor: una columna angosta con el próximo evento destacado (cuenta regresiva y
 * confirmaciones), el resto en filas con íconos de acción y lo ya celebrado plegado al final; sin tarjetas ni párrafos.
 * Cada tipo de cuenta ve lo suyo: el borrador de «Hazlo tú», el cambio por WhatsApp en lo que armó el equipo, el plan
 * y lo que pide atención del revendedor.
 */
class ClientHomeTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    private function guests(int $invitationId, int $confirmed, int $pending, int $declined, ?\DateTimeInterface $confirmedAt = null): void
    {
        foreach ([['confirmed', $confirmed], ['pending', $pending], ['declined', $declined]] as [$status, $count]) {
            for ($i = 0; $i < $count; $i++) {
                Guest::create([
                    'invitation_id' => $invitationId, 'name' => "Invitado {$status} {$i}", 'passes_allocated' => 2,
                    'passes_confirmed' => $status === 'confirmed' ? 2 : 0, 'status' => $status,
                    'confirmed_at' => $status === 'confirmed' ? $confirmedAt : null,
                ]);
            }
        }
    }

    public function test_the_next_event_is_highlighted_with_its_countdown_and_the_numbers_of_its_guests(): void
    {
        $owner = User::factory()->create();
        $next = $this->createInvitation(['user_id' => $owner->id, 'title' => 'XV de Valentina', 'event_date' => now()->addDays(23)->setTime(20, 0), 'package' => 'premium']);
        $later = $this->createInvitation(['user_id' => $owner->id, 'title' => 'Boda de Ana', 'event_date' => now()->addMonths(5), 'package' => 'premium']);
        $this->guests($next->id, 3, 2, 1, now()->subDays(2));

        $page = $this->actingAs($owner)->withoutVite()->get(route('client.dashboard'))->assertOk();

        $page
            // El próximo arriba, con «faltan 23 días»; el otro, en la lista
            ->assertSeeInOrder(['Próximo', 'faltan 23 días', 'XV de Valentina', 'Boda de Ana'], false)
            ->assertSee('ev-next', false)
            ->assertSee('ev-row', false)
            // Los números sueltos y la línea fina
            ->assertSee('<b>3</b> confirmaron', false)
            ->assertSee('<b>2</b> sin responder', false)
            ->assertSee('<b>1</b> no asisten', false)
            ->assertSee('ev-bar', false)
            // La novedad de la semana, calculada con las fechas
            ->assertSee('+3 confirmaciones esta semana')
            // Sin tarjetas ni los párrafos de antes
            ->assertDontSee('admin-card', false)
            ->assertDontSee('Abre tu página, comparte el enlace y revisa quién respondió.')
            ->assertDontSee('Arma otra invitación');
    }

    public function test_a_team_made_event_asks_for_changes_on_whatsapp_and_one_made_by_the_owner_does_not(): void
    {
        $owner = User::factory()->create();
        $this->createInvitation(['user_id' => $owner->id, 'title' => 'XV de Valentina', 'event_date' => now()->addDays(10)]);
        $this->createInvitation(['user_id' => $owner->id, 'is_diy' => true, 'package' => 'gratis', 'title' => 'Mis XV', 'event_date' => now()->addDays(30)]);

        $page = $this->actingAs($owner)->withoutVite()->get(route('client.dashboard'))->assertOk();

        // El pedido de cambio lleva el nombre del evento ya escrito; la que armó el propio dueño no lo trae
        $page->assertSee('title="Pedir un cambio"', false);
        $this->assertSame(1, substr_count($page->getContent(), 'title="Pedir un cambio"'));
        $this->assertStringContainsString(rawurlencode('«XV de Valentina»'), $page->getContent());
        // Mandar el enlace por WhatsApp, en lo que ya está en línea
        $page->assertSee('title="Mandar por WhatsApp"', false)
            ->assertSee('https://wa.me/?text=', false);
    }

    public function test_a_draft_of_hazlo_tu_offers_to_continue_and_publish_and_the_create_button_is_a_plus(): void
    {
        $owner = User::factory()->create();
        $draft = $this->createInvitation(['user_id' => $owner->id, 'is_diy' => true, 'package' => null, 'status' => 'inactive', 'title' => 'Cumple de Sofía', 'event_date' => now()->addDays(40)]);

        $this->actingAs($owner)->withoutVite()->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('Borrador')
            ->assertSee('title="Publicar"', false)
            ->assertSee(route('client.diy.publish', $draft), false)
            ->assertSee('title="Continuar"', false)
            ->assertSee(route('client.diy.edit', $draft), false)
            ->assertSee('title="Borrar borrador"', false)
            // Crear otra: un «+» junto al título, sin banner
            ->assertSee('class="ev-add"', false)
            ->assertSee(route('client.diy.create'), false);
    }

    public function test_an_account_without_events_sees_the_big_button_and_not_the_plus(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)->withoutVite()->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('Arma tu invitación, gratis')
            ->assertSee('Crear mi invitación')
            ->assertDontSee('class="ev-add"', false);
    }

    public function test_what_already_happened_is_folded_at_the_end(): void
    {
        $owner = User::factory()->create();
        $this->createInvitation(['user_id' => $owner->id, 'title' => 'Bautizo de Mateo', 'event_date' => now()->subDays(40)]);
        $this->createInvitation(['user_id' => $owner->id, 'title' => 'XV de Valentina', 'event_date' => now()->addDays(20)]);

        $this->actingAs($owner)->withoutVite()->get(route('client.dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['XV de Valentina', 'Ya celebrados · 1', 'Bautizo de Mateo'], false)
            ->assertSee('<details class="site-enter ev-past"', false);
    }

    public function test_the_event_day_shows_the_door_shortcut_only_when_the_package_has_it(): void
    {
        $owner = User::factory()->create();
        $this->createInvitation(['user_id' => $owner->id, 'title' => 'XV de Valentina', 'event_date' => now()->addDay()->setTime(20, 0), 'package' => 'premium']);

        $this->actingAs($owner)->withoutVite()->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('Abrir el control de entrada')
            ->assertSee('#control-entrada', false);

        $far = User::factory()->create();
        $this->createInvitation(['user_id' => $far->id, 'title' => 'Lejos', 'event_date' => now()->addDays(30), 'package' => 'premium']);

        $this->actingAs($far)->withoutVite()->get(route('client.dashboard'))->assertOk()->assertDontSee('Abrir el control de entrada');
    }

    public function test_the_reseller_sees_the_plan_as_a_line_and_what_needs_attention(): void
    {
        $reseller = User::factory()->reseller('inicial', now()->addDays(20)->toDateString())->create();
        $this->createInvitation(['reseller_id' => $reseller->id, 'user_id' => null, 'title' => 'Cumple de Bruno', 'status' => 'inactive', 'event_date' => now()->addDays(6)]);
        $client = User::factory()->create(['name' => 'Pablo de Rosa']);
        $mine = $this->createInvitation(['reseller_id' => $reseller->id, 'user_id' => $client->id, 'title' => 'XV de Camila', 'event_date' => now()->addDays(9)]);

        $page = $this->actingAs($reseller)->withoutVite()->get(route('client.dashboard'))->assertOk();

        $page
            ->assertSee('ev-plan', false)
            ->assertSee('Plan Inicial')
            ->assertSee('invitaciones publicadas este mes')
            ->assertSee('Por atender')
            ->assertSee('Cumple de Bruno · sin cliente')
            ->assertSee('Cumple de Bruno · sin publicar, faltan 6 días')
            // Cada evento suyo lleva el acceso al editor y el aviso al cliente por WhatsApp con su nombre
            ->assertSee(route('client.invitations.edit', $mine), false)
            ->assertSee(rawurlencode('Hola Pablo de Rosa'), false)
            ->assertDontSee('admin-card', false);
    }

    public function test_an_expired_reseller_is_not_sent_to_the_editor_from_what_needs_attention(): void
    {
        $reseller = User::factory()->reseller('inicial', now()->subDays(20)->toDateString())->create();
        $invitation = $this->createInvitation(['reseller_id' => $reseller->id, 'user_id' => null, 'title' => 'Cumple de Bruno', 'status' => 'inactive', 'event_date' => now()->addDays(6)]);

        $this->actingAs($reseller)->withoutVite()->get(route('client.dashboard'))
            ->assertOk()
            ->assertDontSee('Por atender')
            ->assertDontSee(route('client.invitations.edit', $invitation), false);
    }
}
