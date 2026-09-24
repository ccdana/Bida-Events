<?php

namespace Tests\Feature;

use App\Http\Controllers\SeoController;
use App\Support\Money;
use App\Support\Offers;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * robots.txt, sitemap.xml, llms.txt y la guía pública: lo que leen Google y los motores de respuesta.
 * sitemap y llms.txt se arman con la configuración de hoy (precios y páginas nunca quedan viejos);
 * robots.txt es estático y tiene que seguir la lista de rutas privadas de SeoController.
 */
class SeoResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_robots_hides_every_private_path_and_points_to_the_sitemap(): void
    {
        // Estático (lo sirve nginx y lo usa el healthcheck de Docker): tiene que seguir a PRIVATE_PATHS
        $robots = file_get_contents(public_path('robots.txt'));

        foreach (SeoController::PRIVATE_PATHS as $path) {
            $this->assertMatchesRegularExpression('#^Disallow: '.preg_quote($path, '#').'\r?$#m', $robots);
        }

        $this->assertMatchesRegularExpression('#^Sitemap: https://\S+/sitemap\.xml$#m', $robots);
    }

    public function test_sitemap_and_llms_do_not_open_a_session(): void
    {
        $this->assertEmpty($this->get(route('sitemap'))->headers->getCookies());
        $this->assertEmpty($this->get(route('llms'))->headers->getCookies());
    }

    public function test_the_sitemap_lists_the_guide_diy_and_legal_pages_with_their_dates(): void
    {
        $response = $this->get(route('sitemap'))->assertOk();

        $response->assertSee('<loc>'.route('guide').'</loc>', false)
            ->assertSee('<loc>'.route('diy').'</loc>', false)
            ->assertSee('<loc>'.route('legal', 'privacidad').'</loc>', false)
            ->assertSee('<lastmod>', false)
            ->assertDontSee('/entrada/', false)
            ->assertDontSee('/puerta/', false);
    }

    public function test_llms_txt_summarizes_the_business_with_todays_prices(): void
    {
        $response = $this->get('/llms.txt')->assertOk();

        $this->assertStringStartsWith('text/markdown', $response->headers->get('Content-Type'));
        $content = $response->getContent();

        $this->assertStringStartsWith('# '.config('bida.brand'), $content);
        $this->assertStringContainsString(route('guide'), $content);
        $this->assertStringNotContainsString('&amp;', $content);

        foreach (Offers::packages() as $package) {
            $this->assertStringContainsString('Paquete '.$package['name'].': '.Money::format($package['final_price']), $content);
        }

        foreach (Offers::resellerPlans() as $plan) {
            $this->assertStringContainsString('Plan '.$plan['name'].': '.Money::format($plan['final_price']).' al mes', $content);
        }
    }

    public function test_the_guide_answers_first_and_carries_article_faq_and_breadcrumb_data(): void
    {
        $response = $this->withoutVite()->get(route('guide'))->assertOk();

        $response->assertSee('En pocas palabras')
            ->assertSee('<h1', false)
            ->assertSee('Confirmación de asistencia y control de entrada con QR')
            ->assertSee(Money::format(collect(Offers::packages())->min('final_price')))
            ->assertSee('"@type":"Article"', false)
            ->assertSee('"@type":"FAQPage"', false)
            ->assertSee('"@type":"BreadcrumbList"', false);

        // Una sola h1 por página
        $this->assertSame(1, substr_count($response->getContent(), '<h1'));
    }
}
