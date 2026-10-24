<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\InvitationModuleService;
use App\Support\InvitationDefaults;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * La vista previa del editor se dibuja con una sola petición (PreviewController::render): el editor manda
 * lo que está armando y recibe la invitación lista, sin pasar por la sesión. Antes eran dos peticiones (guardar
 * y pedir el marco) y cada letra tardaba cerca de 2 segundos en verse.
 */
class PreviewRenderTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    private function payload(array $over = [], array $modules = []): string
    {
        $base = json_decode(json_encode(InvitationDefaults::emptyModules()), true);
        $base['bienvenida']['nombre_quinceanera'] = 'Valeria';
        $base['bienvenida']['nombre'] = 'Valeria';

        return json_encode(array_merge([
            'title' => 'Prueba', 'slug' => 'prueba', 'template' => 'invitations.templates.xv-premium', 'locale' => 'es',
            'event_date' => '2027-03-14T19:00', 'expires_at' => '2027-12-31', 'package' => 'estandar',
            'modulos' => array_replace_recursive($base, $modules),
        ], $over));
    }

    private function render(User $user, string $payload)
    {
        return $this->actingAs($user)->withoutVite()->post(route('admin.preview.render'), ['payload' => $payload]);
    }

    public function test_the_invitation_comes_back_drawn_from_what_is_being_edited(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->render($admin, $this->payload())->assertOk()->assertSee('Valeria');

        // Nunca se guarda en el navegador: cada cambio tiene que dibujarse de nuevo
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    public function test_each_change_shows_up_without_saving_anything(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->render($admin, $this->payload(modules: ['bienvenida' => ['nombre_quinceanera' => 'Nombre Cambiado']]))
            ->assertSee('Nombre Cambiado');

        // El interruptor de una parte, igual que con el guardado de siempre
        $on = $this->payload(modules: ['config' => ['modulos' => ['galeria' => true]], 'galeria' => ['fotos' => ['/images/ejemplo/foto-1.svg']]]);
        $off = $this->payload(modules: ['config' => ['modulos' => ['galeria' => false]], 'galeria' => ['fotos' => ['/images/ejemplo/foto-1.svg']]]);
        $this->render($admin, $on)->assertSee('id="galeria"', false);
        $this->render($admin, $off)->assertDontSee('id="galeria"', false);

        // Nada quedó en la sesión: no hay nada que crezca con cada cambio
        $this->assertNull(session('admin_invitation_preview.draft'));
        $this->assertDatabaseCount('invitations', 0);
    }

    public function test_a_photo_that_is_still_in_the_browser_is_shown_as_it_arrives(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->render($admin, $this->payload(modules: ['bienvenida' => ['imagen_hero' => 'blob:http://bida-events.test/1234-abcd']]))
            ->assertOk()
            ->assertSee('blob:http://bida-events.test/1234-abcd', false);
    }

    public function test_a_broken_payload_gets_the_waiting_message_and_not_an_error(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        foreach (['', 'no es json', '[]'] as $bad) {
            $this->render($admin, $bad)->assertOk()->assertSee('Configura la invitación');
        }
    }

    public function test_only_people_with_the_editor_can_ask_for_a_preview(): void
    {
        $client = User::factory()->create(['is_admin' => false]);

        $this->actingAs($client)->post(route('admin.preview.render'), ['payload' => $this->payload()])->assertRedirect();
        $this->post(route('logout'));
        $this->post(route('admin.preview.render'), ['payload' => $this->payload()])->assertRedirect(route('login'));
    }

    public function test_resellers_and_do_it_yourself_accounts_use_their_own_route(): void
    {
        $reseller = User::factory()->reseller()->create();
        $owner = User::factory()->create(['is_admin' => false]);
        $expired = User::factory()->reseller(renewsAt: now()->subMonth(), status: 'expired')->create();

        foreach ([$reseller, $owner] as $user) {
            $this->actingAs($user)->withoutVite()->post(route('client.editor.preview.render'), ['payload' => $this->payload()])
                ->assertOk()
                ->assertSee('Valeria');
        }

        // Un revendedor sin la suscripción al día no usa el editor, tampoco su vista previa
        $this->actingAs($expired)->post(route('client.editor.preview.render'), ['payload' => $this->payload()])->assertRedirect();
    }

    public function test_the_editor_page_points_to_the_single_request_endpoint(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->withoutVite()->get(route('admin.invitations.create'))
            ->assertOk()
            ->assertSee('name="ed-preview-frame"', false)
            ->assertSee('previewRenderUrl', false);
    }
}
