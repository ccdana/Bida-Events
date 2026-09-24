<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Barra lateral de los paneles: el administrador y el revendedor filtran sus invitaciones por lo que
 * son, su estado y (solo el administrador) quién las armó. Los filtros viajan en la URL.
 */
class PanelSidebarFiltersTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public function test_the_admin_filters_by_state_and_origin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $reseller = User::factory()->reseller('aliado')->create();

        $this->createInvitation(['title' => 'Fiesta publicada del equipo']);
        $this->createInvitation(['title' => 'Borrador del equipo', 'status' => 'inactive']);
        $this->createInvitation(['title' => 'Armada por un aliado', 'reseller_id' => $reseller->id, 'event_date' => now()->subWeek()]);

        $this->actingAs($admin)->withoutVite()
            ->get(route('admin.dashboard', ['estado' => 'inactivas']))
            ->assertOk()
            ->assertSee('Borrador del equipo')
            ->assertDontSee('Fiesta publicada del equipo')
            ->assertDontSee('Armada por un aliado');

        $this->actingAs($admin)->withoutVite()
            ->get(route('admin.dashboard', ['origen' => 'revendedores']))
            ->assertOk()
            ->assertSee('Armada por un aliado')
            ->assertDontSee('Borrador del equipo')
            ->assertSee('Quién la armó');

        $this->actingAs($admin)->withoutVite()
            ->get(route('admin.dashboard', ['estado' => 'pasadas']))
            ->assertOk()
            ->assertSee('Armada por un aliado')
            ->assertDontSee('Fiesta publicada del equipo');
    }

    public function test_an_unknown_filter_value_hides_nothing(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->createInvitation(['title' => 'Siempre visible']);

        $this->actingAs($admin)->withoutVite()
            ->get(route('admin.dashboard', ['estado' => 'inventado', 'tipo' => 'no-existe', 'origen' => 'x']))
            ->assertOk()
            ->assertSee('Siempre visible');
    }

    public function test_the_reseller_filters_only_its_own_invitations_without_the_origin_group(): void
    {
        $reseller = User::factory()->reseller('aliado')->create();
        $other = User::factory()->reseller('aliado')->create();

        $this->createInvitation(['title' => 'Mía publicada', 'reseller_id' => $reseller->id]);
        $this->createInvitation(['title' => 'Mía sin publicar', 'reseller_id' => $reseller->id, 'status' => 'inactive']);
        $this->createInvitation(['title' => 'De otro aliado', 'reseller_id' => $other->id, 'status' => 'inactive']);

        $this->actingAs($reseller)->withoutVite()
            ->get(route('client.dashboard', ['estado' => 'inactivas']))
            ->assertOk()
            ->assertSee('Mía sin publicar')
            ->assertDontSee('Mía publicada')
            ->assertDontSee('De otro aliado')
            ->assertDontSee('Quién la armó');
    }
}
