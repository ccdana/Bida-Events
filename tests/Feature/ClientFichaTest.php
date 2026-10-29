<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\User;
use App\Support\GuestListParser;
use App\Support\InvitationTemplates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * La ficha de un evento: su nombre, los números y los íconos arriba; debajo, pestañas con subrayado solo para lo que tiene
 * algo (invitados, fotos, música, encuestas, puerta y, para el revendedor, su cliente); la lista de invitados con filtros,
 * recordatorio por WhatsApp y alta de varios pegando una lista.
 */
class ClientFichaTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public function test_the_pasted_list_is_read_one_guest_per_line_with_optional_phone_and_passes(): void
    {
        $result = GuestListParser::parse("Familia Quispe, 71234567, 3\nAna Pérez\n\nLuis Rojas; 70011223\nMaría, 2\n");

        $this->assertSame([], $result['errors']);
        $this->assertSame([
            ['name' => 'Familia Quispe', 'phone' => '71234567', 'passes_allocated' => 3],
            ['name' => 'Ana Pérez', 'phone' => null, 'passes_allocated' => 1],
            ['name' => 'Luis Rojas', 'phone' => '70011223', 'passes_allocated' => 1],
            ['name' => 'María', 'phone' => null, 'passes_allocated' => 2],
        ], $result['guests']);
    }

    public function test_a_bad_line_is_reported_with_its_number_and_nothing_is_read_as_valid(): void
    {
        $result = GuestListParser::parse("Ana, 3\n, 71234567\nLuis, abc\nPedro, 40\n");

        $this->assertCount(3, $result['errors']);
        $this->assertStringContainsString('Línea 2: falta el nombre.', $result['errors'][0]);
        $this->assertStringContainsString('Línea 3', $result['errors'][1]);
        $this->assertStringContainsString('Línea 4: los pases van de 1 a 20.', $result['errors'][2]);
        $this->assertSame(['Escribe al menos un invitado.'], GuestListParser::parse("  \n\n")['errors']);
        $this->assertNotEmpty(GuestListParser::parse(implode("\n", array_fill(0, 201, 'Ana')))['errors']);
    }

    public function test_the_owner_adds_many_guests_at_once_from_a_pasted_list(): void
    {
        $owner = User::factory()->create();
        $invitation = $this->createInvitation(['user_id' => $owner->id]);

        $this->actingAs($owner)->from(route('client.invitation.show', $invitation))
            ->post(route('client.guests.bulk', $invitation), ['list' => "Familia Quispe, 71234567, 3\nAna Pérez"])
            ->assertRedirect(route('client.invitation.show', $invitation))
            ->assertSessionHas('success', 'Agregaste 2 invitados a tu lista.');

        $this->assertSame(2, Guest::where('invitation_id', $invitation->id)->count());
        $quispe = Guest::where('name', 'Familia Quispe')->firstOrFail();
        $this->assertSame(3, $quispe->passes_allocated);
        $this->assertSame('71234567', $quispe->phone);
        $this->assertSame('pending', $quispe->status);
        $this->assertNotEmpty($quispe->qr_code_token);
    }

    public function test_one_bad_line_adds_nobody_and_keeps_what_was_typed(): void
    {
        $owner = User::factory()->create();
        $invitation = $this->createInvitation(['user_id' => $owner->id]);

        $this->actingAs($owner)->from(route('client.invitation.show', $invitation))
            ->post(route('client.guests.bulk', $invitation), ['list' => "Ana\n, 71234567"])
            ->assertRedirect(route('client.invitation.show', $invitation))
            ->assertSessionHasErrors('list')
            ->assertSessionHasInput('list');

        $this->assertSame(0, Guest::where('invitation_id', $invitation->id)->count());
    }

    public function test_only_the_one_who_manages_the_guests_can_paste_a_list(): void
    {
        $owner = User::factory()->create();
        $invitation = $this->createInvitation(['user_id' => $owner->id]);

        $this->actingAs(User::factory()->create())
            ->post(route('client.guests.bulk', $invitation), ['list' => 'Ana'])
            ->assertRedirect()
            ->assertSessionHas('access_notice');

        $this->assertSame(0, Guest::where('invitation_id', $invitation->id)->count());
    }

    public function test_the_header_has_the_numbers_and_the_icons_and_the_tabs_only_for_what_exists(): void
    {
        $owner = User::factory()->create();
        $invitation = $this->createInvitation(['user_id' => $owner->id, 'title' => 'XV de Valentina', 'event_date' => now()->addDays(23)->setTime(20, 0), 'package' => 'premium']);
        Guest::create(['invitation_id' => $invitation->id, 'name' => 'Familia Quispe', 'phone' => '71234567', 'passes_allocated' => 2, 'passes_confirmed' => 2, 'status' => 'confirmed']);
        Guest::create(['invitation_id' => $invitation->id, 'name' => 'Ana Pérez', 'phone' => '70011223', 'passes_allocated' => 1, 'status' => 'pending']);

        $page = $this->actingAs($owner)->withoutVite()->get(route('client.invitation.show', $invitation))->assertOk();

        $page
            ->assertSee('XV de Valentina')
            ->assertSee('faltan 23 días')
            ->assertSee('<b>1</b> confirmaron', false)
            ->assertSee('<b>1</b> sin responder', false)
            ->assertSee('<b>2</b> personas', false)
            // Los íconos de arriba, con su nombre en el título
            ->assertSee('title="Copiar enlace"', false)
            ->assertSee('title="Mandar por WhatsApp"', false)
            ->assertSee('title="Descargar"', false)
            // Sin fotos, música ni encuestas: no hay pestañas de eso (la puerta sí, es Premium)
            ->assertDontSee('x-show="tab === \'fotos\'"', false)
            ->assertDontSee('x-show="tab === \'musica\'"', false)
            ->assertDontSee('Cómo va tu evento')
            ->assertDontSee('admin-card', false)
            ->assertDontSee('<table', false);
    }

    public function test_every_guest_can_be_reminded_on_whatsapp_only_while_they_have_not_answered(): void
    {
        $owner = User::factory()->create();
        $invitation = $this->createInvitation(['user_id' => $owner->id, 'title' => 'XV de Valentina']);
        Guest::create(['invitation_id' => $invitation->id, 'name' => 'Familia Quispe', 'phone' => '71234567', 'passes_allocated' => 2, 'passes_confirmed' => 2, 'status' => 'confirmed']);
        Guest::create(['invitation_id' => $invitation->id, 'name' => 'Ana Pérez', 'phone' => '70011223', 'passes_allocated' => 1, 'status' => 'pending']);
        Guest::create(['invitation_id' => $invitation->id, 'name' => 'Luis Rojas', 'passes_allocated' => 1, 'status' => 'pending']);

        $page = $this->actingAs($owner)->withoutVite()->get(route('client.invitation.show', $invitation))->assertOk();
        $html = $page->getContent();

        // El recordatorio es solo para quien no respondió y tiene teléfono: uno
        $this->assertSame(1, substr_count($html, 'title="Recordarle que confirme"'));
        $this->assertStringContainsString('https://wa.me/59170011223?text='.rawurlencode('Hola Ana Pérez, te recordamos confirmar'), $html);
        // Mandar el enlace por WhatsApp: a los dos que tienen teléfono
        $this->assertSame(2, substr_count($html, 'title="Mandar su enlace por WhatsApp"'));
        // Los filtros por estado, con su número
        $page->assertSee('aria-label="Filtrar por estado"', false)
            ->assertSeeInOrder(['Todos', '3', 'Confirmaron', '1', 'Sin responder', '2']);
    }

    public function test_the_tabs_appear_for_photos_music_and_the_door_and_the_door_one_opens_from_the_address(): void
    {
        $owner = User::factory()->create();
        $invitation = $this->createInvitation(['user_id' => $owner->id, 'package' => 'premium', 'door_token' => str_repeat('b', 48)]);
        $invitation->contributions()->create(['type' => 'live_photo', 'file_path' => 'https://res.cloudinary.com/demo/image/upload/foto.jpg', 'created_at' => now()]);
        $invitation->contributions()->create(['type' => 'song_request', 'content_text' => 'Un bolero', 'created_at' => now()]);

        $this->actingAs($owner)->withoutVite()->get(route('client.invitation.show', $invitation))
            ->assertOk()
            ->assertSee('role="tablist"', false)
            ->assertSeeInOrder(['Invitados', 'Fotos', 'Música', 'Puerta'])
            // La pestaña sale de la dirección (#puerta) o de la que se usaba al enviar un formulario
            ->assertSee("location.hash.slice(1)", false)
            ->assertSee("sessionStorage.setItem('ficha-tab'", false)
            ->assertSee("x-show=\"tab === 'puerta'\"", false);
    }

    public function test_a_card_starts_with_its_replies_and_has_no_guest_list(): void
    {
        $owner = User::factory()->create();
        $card = $this->createInvitation(['user_id' => $owner->id, 'template' => InvitationTemplates::TARJETA_AMOR, 'title' => 'Tarjeta para Ana']);

        $this->actingAs($owner)->withoutVite()->get(route('client.invitation.show', $card))
            ->assertOk()
            ->assertSee('Respuestas a tu tarjeta')
            ->assertSee("x-show=\"tab === 'respuestas'\"", false)
            ->assertDontSee('Mis invitados')
            // Una sola sección: no hace falta la barra de pestañas
            ->assertDontSee('role="tablist"', false)
            ->assertDontSee('title="Copiar enlace en historias"', false);
    }

    public function test_the_reseller_gets_a_tab_for_the_access_of_the_client_and_the_client_does_not(): void
    {
        $reseller = User::factory()->reseller('inicial', now()->addDays(20)->toDateString())->create();
        $client = User::factory()->create(['name' => 'Pablo de Rosa']);
        $invitation = $this->createInvitation(['reseller_id' => $reseller->id, 'user_id' => $client->id, 'title' => 'XV de Camila']);

        $this->actingAs($reseller)->withoutVite()->get(route('client.invitation.show', $invitation))
            ->assertOk()
            ->assertSee('role="tablist"', false)
            ->assertSeeInOrder(['Invitados', 'Cliente'])
            ->assertSee("x-show=\"tab === 'cliente'\"", false)
            ->assertSee('Pablo de Rosa')
            ->assertSee('title="Editar"', false);

        $this->actingAs($client)->withoutVite()->get(route('client.invitation.show', $invitation))
            ->assertOk()
            ->assertDontSee("x-show=\"tab === 'cliente'\"", false)
            ->assertDontSee('title="Editar"', false);
    }
}
