<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\InvitationModuleService;
use App\Support\Packages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Qué incluye cada paquete (App\Support\Packages): Estándar confirma por WhatsApp, Premium con pase
 * QR, puerta, panel del cliente y descargas; Básico no tiene enlaces personales ni confirmación.
 */
class InvitationPackagesTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    private function invitationWith(?string $package, array $modules = [])
    {
        $invitation = $this->createInvitation(['package' => $package]);

        app(InvitationModuleService::class)->syncAllModules($invitation, array_replace_recursive([
            // La confirmación que corresponde al paquete: por WhatsApp en Estándar, con pase en el resto
            'config' => ['modulos' => ['rsvp' => $package !== Packages::STANDARD, 'rsvp_whatsapp' => $package === Packages::STANDARD, 'encuestas' => true, 'regalos' => true]],
        ], $modules));

        return $invitation->fresh();
    }

    public function test_the_package_decides_each_feature(): void
    {
        $this->assertTrue(Packages::allowsModule(Packages::STANDARD, 'regalos'));
        $this->assertFalse(Packages::allowsModule(Packages::BASIC, 'regalos'));
        $this->assertFalse(Packages::allowsModule(Packages::STANDARD, 'encuestas'));
        $this->assertTrue(Packages::allowsModule(null, 'encuestas'), 'Sin paquete: todo incluido');

        // Dos confirmaciones: por WhatsApp desde Estándar, con pase QR en Premium
        $this->assertTrue(Packages::allowsModule(Packages::STANDARD, 'rsvp_whatsapp'));
        $this->assertFalse(Packages::allowsModule(Packages::STANDARD, 'rsvp'));
        $this->assertTrue(Packages::allowsModule(Packages::PREMIUM, 'rsvp'));
        $this->assertFalse(Packages::allowsModule(Packages::BASIC, 'rsvp_whatsapp'));
        $this->assertSame([Packages::RSVP_PASS, Packages::RSVP_WHATSAPP], Packages::rsvpModesFor($this->createInvitation(['package' => Packages::PREMIUM])));
        $this->assertSame([], Packages::rsvpModesFor($this->createInvitation(['package' => Packages::BASIC])));

        $this->assertFalse(Packages::allows(Packages::STANDARD, 'door'));
        $this->assertTrue(Packages::allows(Packages::PREMIUM, 'client_panel'));
    }

    public function test_standard_confirms_by_whatsapp_and_hides_premium_sections(): void
    {
        $invitation = $this->invitationWith(Packages::STANDARD, ['rsvp_whatsapp' => ['whatsapp' => '+591 71234567']]);
        $guest = $invitation->guests()->create(['name' => 'Familia Rojas', 'passes_allocated' => 3]);

        // En el enlace general se confirma por WhatsApp escribiendo el nombre
        $this->withoutVite()->get(route('invitation.show', $invitation->slug))
            ->assertOk()
            ->assertSee('rsvpWhatsapp(', false)
            ->assertSee('59171234567')
            ->assertDontSee('id="encuestas"', false);

        // En el personal, con el nombre y los lugares; sin estado guardado ni pase
        $this->withoutVite()->get(route('invitation.guest', [$invitation->slug, $guest->qr_code_token]))
            ->assertOk()
            ->assertSee('Familia Rojas')
            ->assertSee('rsvpWhatsapp(', false)
            ->assertDontSee('rsvpForm(', false);

        // La respuesta con pase no existe en Estándar
        $this->postJson("/p/{$invitation->slug}/i/{$guest->qr_code_token}/confirm", ['status' => 'confirmed', 'passes_confirmed' => 1])
            ->assertNotFound();
    }

    public function test_standard_without_a_number_does_not_show_the_confirmation(): void
    {
        $invitation = $this->invitationWith(Packages::STANDARD);

        $this->withoutVite()->get(route('invitation.show', $invitation->slug))
            ->assertOk()
            ->assertDontSee('rsvpWhatsapp(', false);
    }

    public function test_basic_personal_links_open_the_general_invitation(): void
    {
        $invitation = $this->invitationWith(Packages::BASIC);
        $guest = $invitation->guests()->create(['name' => 'Tía Carmen', 'passes_allocated' => 2]);

        $this->get(route('invitation.guest', [$invitation->slug, $guest->qr_code_token]))
            ->assertRedirect(route('invitation.show', $invitation->slug));
    }

    public function test_premium_keeps_the_pass_and_the_client_panel(): void
    {
        $client = User::factory()->create();
        $invitation = $this->invitationWith(Packages::PREMIUM);
        $invitation->update(['user_id' => $client->id]);
        $guest = $invitation->guests()->create(['name' => 'Familia Pérez', 'passes_allocated' => 2]);

        $this->withoutVite()->get(route('invitation.guest', [$invitation->slug, $guest->qr_code_token]))
            ->assertOk()
            ->assertSee('rsvpForm(', false);

        $this->actingAs($client)->withoutVite()
            ->get(route('client.invitation.show', $invitation))
            ->assertOk();
    }

    public function test_the_client_panel_door_and_downloads_are_premium_only(): void
    {
        $client = User::factory()->create();
        $invitation = $this->invitationWith(Packages::STANDARD);
        $invitation->update(['user_id' => $client->id]);

        $this->actingAs($client)->withoutVite()->get(route('client.invitation.show', $invitation))->assertForbidden();
        $this->actingAs($client)->post(route('client.export.store', [$invitation, 'invitation-pdf']))->assertForbidden();

        $this->actingAs($client)->withoutVite()->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('vienen en el paquete Premium');

        // La puerta de una invitación Estándar no abre aunque tenga un enlace
        $invitation->forceFill(['door_token' => str_repeat('a', 48)])->saveQuietly();
        $this->get(route('door.open', str_repeat('a', 48)))->assertNotFound();
    }

    public function test_the_admin_saves_the_package_and_the_editor_knows_it(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->withoutVite()->get(route('admin.invitations.create'))
            ->assertOk()
            ->assertSee('packageModules', false);

        $invitation = $this->createInvitation();
        $this->actingAs($admin)->put(route('admin.invitations.update', $invitation), [
            'title' => $invitation->title,
            'slug' => $invitation->slug,
            'template' => $invitation->template,
            'event_type_id' => $invitation->event_type_id,
            'event_date' => $invitation->event_date->format('Y-m-d H:i'),
            'expires_at' => $invitation->expires_at->format('Y-m-d'),
            'status' => 'active',
            'package' => Packages::PREMIUM,
        ])->assertSessionHasNoErrors();

        $this->assertSame(Packages::PREMIUM, $invitation->fresh()->package);

        $this->actingAs($admin)->put(route('admin.invitations.update', $invitation), [
            'title' => $invitation->title,
            'slug' => $invitation->slug,
            'template' => $invitation->template,
            'event_type_id' => $invitation->event_type_id,
            'event_date' => $invitation->event_date->format('Y-m-d H:i'),
            'expires_at' => $invitation->expires_at->format('Y-m-d'),
            'status' => 'active',
            'package' => 'oro',
        ])->assertSessionHasErrors('package');
    }
}
