<?php

namespace Tests\Feature;

use App\Http\Controllers\Public\DoorController;
use App\Models\Invitation;
use App\Models\User;
use App\Support\GuestPass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Control de entrada el día del evento: el QR del pase se valida en la puerta, cada persona entra
 * una sola vez y solo los teléfonos con el enlace de puerta pueden registrar ingresos.
 */
class DoorCheckInTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public function test_the_organizer_shares_a_door_link_and_the_door_lets_each_pass_in_once(): void
    {
        $client = User::factory()->create(['is_admin' => false]);
        $invitation = $this->createInvitation(['user_id' => $client->id]);
        $guest = $invitation->guests()->create(['name' => 'Familia Quispe', 'passes_allocated' => 4, 'passes_confirmed' => 3, 'status' => 'confirmed']);

        $this->actingAs($client)->post(route('client.door.store', $invitation))->assertRedirect();
        $doorToken = $invitation->fresh()->door_token;
        $this->assertNotNull($doorToken);

        $this->actingAs($client)->withoutVite()->get(route('client.invitation.show', $invitation))
            ->assertOk()
            ->assertSee('Control de entrada')
            ->assertSee(route('door.open', $doorToken), false);

        auth()->logout();

        // El personal de la puerta abre su enlace: queda habilitado en ese teléfono
        $this->withoutVite()->get(route('door.open', $doorToken))
            ->assertOk()
            ->assertSee('Escanea el pase')
            ->assertCookie(DoorController::cookieName($invitation));

        $door = fn () => $this->withCookie(DoorController::cookieName($invitation), $doorToken)->withoutVite();

        $door()->get(GuestPass::url($invitation, $guest))
            ->assertOk()
            ->assertSee('Puede pasar')
            ->assertSee('Familia Quispe');

        // Entran 2 de 3 y después el último
        $door()->post(route('door.check-in', [$invitation->slug, $guest->qr_code_token]), ['people' => 2])->assertRedirect();
        $this->assertSame(2, $guest->fresh()->checked_in_passes);
        $this->assertNotNull($guest->fresh()->checked_in_at);

        $door()->post(route('door.check-in', [$invitation->slug, $guest->qr_code_token]), ['people' => 5])->assertRedirect();
        $this->assertSame(3, $guest->fresh()->checked_in_passes);

        // El pase ya no deja entrar a nadie más
        $door()->get(GuestPass::url($invitation, $guest))->assertSee('Pase ya usado');
        $door()->post(route('door.check-in', [$invitation->slug, $guest->qr_code_token]), ['people' => 1])
            ->assertSessionHas('door_error');
        $this->assertSame(3, $guest->fresh()->checked_in_passes);

        $this->assertSame(['expected' => 3, 'arrived' => 3, 'passesUsed' => 1], DoorController::stats($invitation));
    }

    public function test_without_the_door_link_the_qr_only_opens_the_guest_invitation(): void
    {
        $invitation = $this->withDoor($this->createInvitation(), str_repeat('a', 48));
        $guest = $invitation->guests()->create(['name' => 'Ana', 'passes_allocated' => 1]);

        $this->get(GuestPass::url($invitation, $guest))
            ->assertRedirect(route('invitation.guest', [$invitation->slug, $guest->qr_code_token]));

        $this->post(route('door.check-in', [$invitation->slug, $guest->qr_code_token]), ['people' => 1])->assertForbidden();
        $this->assertSame(0, $guest->fresh()->checked_in_passes);
    }

    public function test_a_new_door_link_locks_out_the_previous_phones(): void
    {
        $client = User::factory()->create(['is_admin' => false]);
        $invitation = $this->withDoor($this->createInvitation(['user_id' => $client->id]), $old = str_repeat('b', 48));
        $this->get(route('door.open', $old))->assertOk();
        $guest = $invitation->guests()->create(['name' => 'Luis', 'passes_allocated' => 1]);

        $this->actingAs($client)->post(route('client.door.store', $invitation));
        auth()->logout();

        $this->get(route('door.open', $old))->assertNotFound();
        $this->withCookie(DoorController::cookieName($invitation), $old)
            ->get(GuestPass::url($invitation, $guest))
            ->assertRedirect(route('invitation.guest', [$invitation->slug, $guest->qr_code_token]));
    }

    public function test_the_short_code_finds_the_pass_when_the_qr_cannot_be_read(): void
    {
        $invitation = $this->withDoor($this->createInvitation(), $door = str_repeat('c', 48));
        $guest = $invitation->guests()->create(['name' => 'Rosa', 'passes_allocated' => 2]);

        $this->withCookie(DoorController::cookieName($invitation), $door)
            ->post(route('door.lookup', $door), ['code' => strtolower(GuestPass::code($guest))])
            ->assertRedirect(GuestPass::url($invitation, $guest));

        $this->withCookie(DoorController::cookieName($invitation), $door)
            ->post(route('door.lookup', $door), ['code' => 'ZZZZ9999'])
            ->assertSessionHas('door_error');
    }

    public function test_the_guest_pass_qr_carries_the_entry_link(): void
    {
        $invitation = $this->createInvitation();
        $guest = $invitation->guests()->create(['name' => 'Carla', 'passes_allocated' => 2, 'passes_confirmed' => 2, 'status' => 'confirmed']);

        $this->assertStringContainsString('/entrada/'.$invitation->slug.'/'.$guest->qr_code_token, GuestPass::url($invitation, $guest));

        $this->assertStringStartsWith('<svg', trim(preg_replace('/^<\?xml[^>]*>/', '', GuestPass::svg($invitation, $guest))));
        $this->assertSame(strtoupper(substr($guest->qr_code_token, 0, 8)), GuestPass::code($guest));
    }

    private function withDoor(Invitation $invitation, string $token): Invitation
    {
        $invitation->forceFill(['door_token' => $token])->save();

        return $invitation;
    }
}
