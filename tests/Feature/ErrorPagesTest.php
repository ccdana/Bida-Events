<?php

namespace Tests\Feature;

use App\Models\EventType;
use App\Models\Invitation;
use Database\Seeders\EventTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Los errores muestran una página con la marca y un camino de vuelta, no la del framework («Not Found»).
 */
class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_missing_page_is_branded_and_leads_home(): void
    {
        $this->get('/esta-pagina-no-existe')
            ->assertNotFound()
            ->assertSee('No encontramos esa página')
            ->assertSee('Ir al inicio')
            ->assertSee('href="'.url('/').'"', false)
            ->assertSee('noindex', false)
            ->assertDontSee('Not Found');
    }

    public function test_an_invitation_link_that_does_not_open_explains_it_to_the_guest(): void
    {
        // Mal escrita o vencida: el mismo mensaje, sin decir cuál de las dos
        foreach (['/p/no-existe', '/p/no-existe/i/token-falso', '/muestra/no-existe'] as $path) {
            $this->get($path)
                ->assertNotFound()
                ->assertSee('Esta invitación no está disponible')
                ->assertSee('pídele uno nuevo a quien te invitó')
                ->assertSee('Conocer Bida Events')
                ->assertDontSee('Not Found');
        }
    }

    public function test_an_expired_invitation_looks_the_same_as_a_missing_one(): void
    {
        $this->createInvitationForExpiry();

        $expired = $this->get('/p/vencida')->assertNotFound()->getContent();
        $missing = $this->get('/p/inventada')->assertNotFound()->getContent();

        $this->assertSame(
            preg_replace('/\s+/', ' ', strip_tags($missing)),
            preg_replace('/\s+/', ' ', strip_tags($expired)),
        );
    }

    public function test_a_door_link_that_does_not_work_has_its_own_message(): void
    {
        $this->get('/puerta/token-que-no-existe')
            ->assertNotFound()
            ->assertSee('Este enlace de la puerta no sirve');
    }

    public function test_the_english_site_gets_english_errors(): void
    {
        $this->get('/en/nothing-here')
            ->assertNotFound()
            ->assertSee('find that page')
            ->assertSee('Go to the home page')
            ->assertSee('href="'.url('/en').'"', false)
            ->assertSee('<html lang="en">', false)
            ->assertDontSee('No encontramos');
    }

    public function test_every_error_code_has_a_branded_page(): void
    {
        foreach ([403 => 'No tienes acceso', 419 => 'La página caducó', 429 => 'Demasiados intentos', 500 => 'Algo salió mal', 503 => 'Estamos en mantenimiento'] as $code => $text) {
            Route::get("/_error/{$code}", fn () => abort($code));

            $this->get("/_error/{$code}")
                ->assertStatus($code)
                ->assertSee($text)
                ->assertSee('Bida')
                ->assertDontSee('Server Error');
        }
    }

    public function test_the_back_button_only_appears_for_pages_of_this_site(): void
    {
        $this->get('/esta-pagina-no-existe', ['Referer' => url('/precios')])
            ->assertSee('href="'.url('/precios').'"', false)
            ->assertSee('Volver');

        $this->get('/esta-pagina-no-existe', ['Referer' => 'https://otro-sitio.example/pagina'])
            ->assertDontSee('otro-sitio.example')
            ->assertDontSee('Volver');
    }

    public function test_json_requests_keep_their_json_error(): void
    {
        $this->getJson('/p/no-existe/fotomural')->assertNotFound()->assertHeader('Content-Type', 'application/json');
    }

    public function test_the_error_page_needs_nothing_from_the_site_to_render(): void
    {
        $html = $this->get('/esta-pagina-no-existe')->getContent();

        // Sin los estilos ni scripts del sitio (un error puede ocurrir cuando eso falla)
        $this->assertStringNotContainsString('/build/', $html);
        $this->assertStringNotContainsString('<script', $html);
    }

    private function createInvitationForExpiry(): void
    {
        $this->app->make(EventTypeSeeder::class)->run();

        Invitation::create([
            'event_type_id' => EventType::query()->value('id'),
            'slug' => 'vencida',
            'template' => 'invitations.templates.xv-premium',
            'title' => 'Vencida',
            'event_date' => now()->subMonths(8),
            'status' => 'active',
            'expires_at' => now()->subMonth(),
        ]);
    }
}
