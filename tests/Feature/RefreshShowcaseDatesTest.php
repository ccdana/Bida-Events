<?php

namespace Tests\Feature;

use App\Models\Invitation;
use Carbon\Carbon;
use Database\Seeders\ShowcaseInvitationsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Las muestras de la portada se renuevan solas: una muestra con la fecha ya pasada se vería como un evento
 * terminado, sin cuenta regresiva ni confirmación.
 */
class RefreshShowcaseDatesTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_the_command_moves_a_showcase_whose_date_got_close_and_leaves_the_rest(): void
    {
        Carbon::setTestNow('2026-10-21 09:00');
        (new ShowcaseInvitationsSeeder)->seedSlug('cumple-daniela-30');

        $sample = Invitation::where('slug', 'cumple-daniela-30')->firstOrFail();
        $this->assertSame('2026-12-05', $sample->event_date->toDateString());
        $this->assertFalse($sample->is_post_event);

        // Hoy no hay nada que mover
        $this->artisan('bida:renovar-muestras')->expectsOutputToContain('al día')->assertSuccessful();

        // Pasan las semanas: la fecha quedó a menos de 45 días y se corre sola
        Carbon::setTestNow('2026-12-10 09:00');
        $this->artisan('bida:renovar-muestras')->expectsOutputToContain('1 muestras renovadas')->assertSuccessful();

        $sample->refresh();
        $this->assertSame('2027-01-30', $sample->event_date->toDateString());
        $this->assertFalse($sample->is_post_event);
        $this->assertTrue(Invitation::published()->where('slug', 'cumple-daniela-30')->exists());

        $this->artisan('bida:renovar-muestras')->expectsOutputToContain('al día')->assertSuccessful();
    }

    public function test_the_showcase_page_shows_the_countdown_instead_of_the_finished_event(): void
    {
        Carbon::setTestNow('2026-10-21 09:00');
        (new ShowcaseInvitationsSeeder)->seedSlug('cumple-daniela-30');

        $this->withoutVite()->get(route('invitation.demo', 'cumple-daniela-30'))
            ->assertOk()
            ->assertSee('id="cuenta-regresiva"', false)
            ->assertSee('id="rsvp"', false)
            ->assertSee('Sábado 5 de diciembre')
            ->assertDontSee('id="post-evento"', false);
    }

    public function test_dry_run_reports_without_changing_anything(): void
    {
        Carbon::setTestNow('2026-10-21 09:00');
        (new ShowcaseInvitationsSeeder)->seedSlug('cumple-daniela-30');

        Carbon::setTestNow('2026-12-10 09:00');
        $this->artisan('bida:renovar-muestras --dry-run')->expectsOutputToContain('cambiarían')->assertSuccessful();

        $this->assertSame('2026-12-05', Invitation::where('slug', 'cumple-daniela-30')->firstOrFail()->event_date->toDateString());
    }
}
