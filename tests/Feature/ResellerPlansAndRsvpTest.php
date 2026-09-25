<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\InvitationModuleService;
use App\Support\Packages;
use App\Support\ResellerSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Planes de revendedor y las dos formas de confirmar: el catálogo crece con cada plan (Inicial:
 * lienzo y clásicas; Aliado: más las de temporada; Emprendedor y Agencia: más las nuevas), la
 * confirmación por WhatsApp llega desde Emprendedor y el pase QR con la puerta en Agencia. Una
 * invitación confirma de una sola forma. El revendedor cambia su contraseña.
 */
class ResellerPlansAndRsvpTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    private function resellerInvitation(string $plan, array $flags)
    {
        $reseller = User::factory()->reseller($plan)->create();
        $invitation = $this->createInvitation(['reseller_id' => $reseller->id]);

        app(InvitationModuleService::class)->syncAllModules($invitation, [
            'config' => ['modulos' => $flags],
            'rsvp_whatsapp' => ['whatsapp' => '59171234567'],
        ]);

        return [$reseller, $invitation->fresh()];
    }

    public function test_each_plan_adds_its_templates(): void
    {
        $collections = fn (string $plan) => collect(ResellerSubscription::allowedTemplates(User::factory()->reseller($plan)->make()))
            ->pluck('collection')->unique()->sort()->values()->all();

        $this->assertSame(['clasica', 'lienzo'], $collections('inicial'));
        $this->assertSame(['clasica', 'lienzo', 'temporada'], $collections('aliado'));
        $this->assertContains('nueva', config('bida.reseller_plans.emprendedor.collections'));
        $this->assertContains('nueva', config('bida.reseller_plans.agencia.collections'));
    }

    public function test_the_whatsapp_confirmation_comes_with_emprendedor_and_the_qr_pass_with_agencia(): void
    {
        [, $aliado] = $this->resellerInvitation('aliado', ['rsvp_whatsapp' => true]);
        $this->assertSame([], Packages::rsvpModesFor($aliado));
        $this->withoutVite()->get(route('invitation.show', $aliado->slug))->assertOk()->assertDontSee('rsvpWhatsapp(', false);

        [, $emprendedor] = $this->resellerInvitation('emprendedor', ['rsvp_whatsapp' => true]);
        $this->assertSame([Packages::RSVP_WHATSAPP], Packages::rsvpModesFor($emprendedor));
        $this->assertFalse(Packages::allowsFor($emprendedor, 'door'));
        $this->withoutVite()->get(route('invitation.show', $emprendedor->slug))->assertOk()->assertSee('rsvpWhatsapp(', false);

        [, $agencia] = $this->resellerInvitation('agencia', ['rsvp' => true]);
        $this->assertTrue(Packages::allowsFor($agencia, 'rsvp_pass'));
        $this->assertTrue(Packages::allowsFor($agencia, 'door'));
        $guest = $agencia->guests()->create(['name' => 'Familia Rojas', 'passes_allocated' => 2]);
        $this->postJson("/p/{$agencia->slug}/i/{$guest->qr_code_token}/confirm", ['status' => 'confirmed', 'passes_confirmed' => 2])->assertOk();

        // En Emprendedor no hay pase: la respuesta guardada no existe
        $emprendedorGuest = $emprendedor->guests()->create(['name' => 'Otra familia', 'passes_allocated' => 2]);
        $this->postJson("/p/{$emprendedor->slug}/i/{$emprendedorGuest->qr_code_token}/confirm", ['status' => 'confirmed', 'passes_confirmed' => 1])->assertNotFound();
    }

    public function test_an_invitation_confirms_in_only_one_way(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $invitation = $this->createInvitation(['package' => Packages::PREMIUM]);

        $this->actingAs($admin)
            ->put(route('admin.invitations.update', $invitation), [
                'title' => $invitation->title,
                'slug' => $invitation->slug,
                'template' => $invitation->template,
                'event_type_id' => $invitation->event_type_id,
                'event_date' => now()->addMonth()->toDateTimeString(),
                'status' => 'active',
                'package' => Packages::PREMIUM,
                'expires_at' => now()->addMonths(3)->toDateString(),
                'modulos' => ['config' => json_encode(['modulos' => ['rsvp' => true, 'rsvp_whatsapp' => true]])],
            ])
            ->assertSessionHasErrors('modulos');
    }

    public function test_the_reseller_changes_the_password(): void
    {
        $reseller = User::factory()->reseller('aliado')->create(['password' => Hash::make('clave-que-le-dimos')]);

        $this->actingAs($reseller)->withoutVite()->get(route('client.account'))->assertOk()->assertSee('Cambiar contraseña');

        $this->actingAs($reseller)
            ->put(route('client.account.password'), ['current_password' => 'otra-cosa', 'password' => 'mi-clave-nueva', 'password_confirmation' => 'mi-clave-nueva'])
            ->assertSessionHasErrors('current_password');

        $this->actingAs($reseller)
            ->put(route('client.account.password'), ['current_password' => 'clave-que-le-dimos', 'password' => 'mi-clave-nueva', 'password_confirmation' => 'mi-clave-nueva'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('mi-clave-nueva', $reseller->fresh()->password));

        // Un cliente común no tiene esta página
        $this->actingAs(User::factory()->create())->get(route('client.account'))->assertForbidden();
    }
}
