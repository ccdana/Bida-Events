<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\InvitationTemplates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Punto 24: la referencia interna de colores, tipografía y componentes.
 * Es una página del panel, así que no puede quedar abierta a cualquiera.
 */
class DesignSystemPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_admin_sees_every_theme_with_its_measured_contrast(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)
            ->withoutVite()
            ->get(route('admin.design-system'))
            ->assertOk()
            ->assertSee('Sistema visual')
            ->assertSee('--site-accent')
            // Los tonos derivados se muestran con su nombre real de CSS
            ->assertSee('--inv-muted', false)
            ->assertSee('--inv-accent-deco', false);

        foreach (InvitationTemplates::all() as $meta) {
            $response->assertSee($meta['label']);
            $response->assertSee($meta['palette']['background']);
        }

        // Las cuatro paletas cumplen AA, así que ninguna aparece marcada
        $response->assertDontSee('por revisar');
    }

    public function test_a_client_cannot_open_it(): void
    {
        $client = User::factory()->create(['is_admin' => false]);

        // Sin sesión, al login; con sesión de cliente, sin permiso
        $this->get(route('admin.design-system'))->assertRedirect(route('login'));
        $this->actingAs($client)->get(route('admin.design-system'))->assertForbidden();
    }
}
