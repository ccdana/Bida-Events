<?php

namespace Tests\Feature;

use App\Models\EventType;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * El panel no debe crecer en consultas ni en memoria a medida que hay más invitaciones e invitados:
 * por eso pagina y cuenta en la base en vez de traerlo todo.
 */
class AdminPanelPerformanceTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public function test_the_dashboard_paginates_and_does_not_grow_with_the_data(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $eventType = EventType::firstOrCreate(['slug' => 'xv-anos'], ['name' => 'XV Años']);

        // 25 invitaciones con 20 invitados cada una: 500 filas que no deberían viajar
        foreach (range(1, 25) as $index) {
            $invitation = Invitation::create([
                'event_type_id' => $eventType->id,
                'slug' => 'evento-'.Str::lower(Str::random(6)),
                'template' => 'invitations.templates.xv-premium',
                'title' => "Evento {$index}",
                'event_date' => now()->addMonths(2),
                'status' => 'active',
                'expires_at' => now()->addMonths(6),
            ]);

            $invitation->guests()->createMany(
                collect(range(1, 20))->map(fn (int $n) => ['name' => "Invitado {$n}", 'passes_allocated' => 2])->all()
            );
        }

        $queries = 0;
        DB::listen(function () use (&$queries) {
            $queries++;
        });

        $response = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();

        // Una página son 20 invitaciones y las cifras salen de contar en la base
        $this->assertSame(20, substr_count($response->getContent(), 'admin-status-badge'));
        $response->assertSee('500');
        $response->assertSee('Página 1 de 2');
        $this->assertLessThan(15, $queries, "El panel hizo {$queries} consultas: debería contar en la base y paginar");
    }

    public function test_the_guest_list_paginates_and_can_be_searched(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $invitation = $this->createInvitation();

        $invitation->guests()->createMany(
            collect(range(1, 60))->map(fn (int $n) => ['name' => "Familia {$n}", 'passes_allocated' => 2])->all()
        );
        $invitation->guests()->create(['name' => 'Ana Quispe', 'passes_allocated' => 1, 'status' => 'confirmed']);

        $this->actingAs($admin);

        // Primera página: 50 de 61
        $this->get(route('admin.guests.index', $invitation))
            ->assertOk()
            ->assertSee('61 invitados')
            ->assertSee('Página 1 de 2');

        // Buscar por nombre
        $this->get(route('admin.guests.index', $invitation).'?q=Ana')
            ->assertOk()
            ->assertSee('Ana Quispe')
            ->assertDontSee('Familia 1<');

        // Filtrar por estado
        $this->get(route('admin.guests.index', $invitation).'?estado=confirmed')
            ->assertOk()
            ->assertSee('Ana Quispe')
            ->assertDontSee('Familia 2<');
    }
}
