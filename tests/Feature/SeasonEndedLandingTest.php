<?php

namespace Tests\Feature;

use Database\Seeders\ShowcaseInvitationsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cuando pasa la fecha de una temporada (Ajustes), su página deja de vender: avisa que terminó, deja probar las
 * muestras y anota a quien quiere que le avisen cuando vuelva.
 */
class SeasonEndedLandingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-10-21 12:00:00');
    }

    public function test_an_ended_season_page_becomes_a_waiting_list(): void
    {
        config(['bida.seasons.amor.ends_at' => '2026-09-21 23:59:59']);

        $waitlist = 'https://wa.me/59160385990?text='.rawurlencode('Hola Bida Events, avísenme cuando vuelva la temporada de Tarjetas del Día del Amor.');

        $html = $this->withoutVite()->get('/tarjetas-dia-del-amor')
            ->assertOk()
            ->assertSee('La temporada terminó.')
            ->assertSee('Las muestras siguen abiertas para que las pruebes.')
            ->assertSee('Avísame cuando vuelva')
            ->assertSee('Te avisamos por WhatsApp cuando vuelva la temporada.')
            ->assertSee('site-how--solo', false)
            ->assertDontSee('Ver precios')
            ->assertDontSee('Lista el mismo día')
            ->assertDontSee('La quiero por')
            ->assertDontSee('id="como"', false)
            ->assertDontSee('Mándanos tu foto y tu mensaje')
            ->assertDontSee('Si la pides el 21')
            ->getContent();

        // El botón de la portada y el del bloque de precio llevan a anotarse, con el código de origen de la página
        $this->assertSame(2, substr_count(html_entity_decode($html), 'wa.me/59160385990?text='.rawurlencode('Hola Bida Events, avísenme cuando vuelva la temporada de Tarjetas del Día del Amor.')));
        $this->assertStringContainsString('Ref.%20AMOR', $html);
        $this->assertStringContainsString('La temporada terminó. Escríbenos y te avisamos de la próxima.', html_entity_decode($html));
    }

    public function test_halloween_works_the_same_once_its_date_passes(): void
    {
        config(['bida.seasons.halloween.ends_at' => '2026-10-20 23:59:59']);

        $this->withoutVite()->get('/invitaciones-de-halloween')
            ->assertOk()
            ->assertSee('La temporada terminó.')
            ->assertSee('Avísame cuando vuelva')
            ->assertDontSee('Mándanos tus datos')
            ->assertDontSee('id="como"', false);
    }

    public function test_a_season_that_is_on_keeps_selling_as_before(): void
    {
        // Una temporada se vende si su fecha no pasó y tiene muestras sembradas
        $seeder = new ShowcaseInvitationsSeeder;
        $seeder->seedSlug('tarjeta-ana-luis');
        $seeder->seedSlug('halloween-noche-diego');
        config(['bida.seasons.amor.ends_at' => '2026-10-25 23:59:59', 'bida.seasons.halloween.ends_at' => '2026-10-31 23:59:59']);

        $this->withoutVite()->get('/tarjetas-dia-del-amor')
            ->assertOk()
            ->assertSee('Ver precios')
            ->assertSee('La quiero por')
            ->assertSee('id="como"', false)
            ->assertSee('Mándanos tu foto y tu mensaje')
            ->assertDontSee('La temporada terminó.')
            ->assertDontSee('Avísame cuando vuelva');

        $this->withoutVite()->get('/invitaciones-de-halloween')
            ->assertOk()
            ->assertSee('Mándanos tus datos')
            ->assertDontSee('Avísame cuando vuelva');
    }

    public function test_the_regular_event_pages_never_show_the_ended_notice(): void
    {
        config(['bida.seasons.amor.ends_at' => '2026-09-21 23:59:59', 'bida.seasons.halloween.ends_at' => '2026-10-20 23:59:59']);

        $this->withoutVite()->get('/invitaciones-de-boda')
            ->assertOk()
            ->assertDontSee('La temporada terminó.')
            ->assertDontSee('Avísame cuando vuelva')
            ->assertSee('Crea tu cuenta y ármala');
    }

    public function test_the_english_waiting_list(): void
    {
        config(['bida.seasons.amor.ends_at' => '2026-09-21 23:59:59']);

        $this->withoutVite()->get('/en/love-day-cards')
            ->assertOk()
            ->assertSee('The season is over.')
            ->assertSee('Let me know when it is back')
            ->assertSee('let you know on WhatsApp when the season is back.')
            ->assertDontSee('Avísame');
    }
}
