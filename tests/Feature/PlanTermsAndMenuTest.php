<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\LemonSqueezyPayloads;
use Tests\Concerns\LibelulaApi;
use Tests\TestCase;

/**
 * Lo que se dice de cambiar y cancelar un plan es lo mismo en /precios, /unete y los Términos, y cambia
 * con lo que el sitio puede hacer (tarjeta, QR o nada en línea). El menú es el mismo en todo el sitio y la
 * oferta para profesionales se llama «Planes».
 */
class PlanTermsAndMenuTest extends TestCase
{
    use LemonSqueezyPayloads, LibelulaApi, RefreshDatabase;

    private function faq(string $html, string $question): string
    {
        $text = html_entity_decode(strip_tags(preg_replace('#<(script|style)\b.*?</\1>#is', '', $html)));
        $text = preg_replace('/\s+/', ' ', $text);

        // De la pregunta a la frase con la que cierra la respuesta
        return trim(Str::before(Str::after($text, $question), 'Lo que ya publicaste sigue en línea.'));
    }

    public function test_without_online_payments_every_page_says_to_write_to_us(): void
    {
        config(['payments.enabled' => false]);

        $plans = $this->withoutVite()->get('/unete')->assertOk();
        $prices = $this->withoutVite()->get('/precios')->assertOk();

        foreach ([$plans, $prices] as $page) {
            $page->assertSee('¿Puedo cambiar de plan o cancelarlo?')
                ->assertSee('Escríbenos por WhatsApp o al correo')
                ->assertSee('solo no lo renuevas')
                ->assertDontSee('desde «Mi plan»')
                ->assertDontSee('portal de pagos');
        }

        $prices->assertSee('El pago lo coordinamos contigo por WhatsApp.')->assertDontSee('con tarjeta se renueva solo');

        $this->withoutVite()->get('/legal/terminos')
            ->assertOk()
            ->assertSee('Para cambiar de plan o dejarlo, escríbenos a')
            ->assertSee('No devolvemos meses parciales')
            ->assertDontSee('se renueva sola')
            ->assertDontSee('desde «Mi plan»');
    }

    public function test_with_card_payments_the_pages_point_to_my_plan_and_the_portal(): void
    {
        $this->configureLemonSqueezy();

        $this->withoutVite()->get('/unete')->assertOk()
            ->assertSee('Con tarjeta lo haces tú desde «Mi plan»')
            ->assertSee('portal de pagos')
            ->assertDontSee('Con QR no hay cobro automático');

        // Los planes y su pago están en /precios: ahí se dice que con tarjeta se renueva solo
        $this->withoutVite()->get('/precios')->assertOk()
            ->assertSee('Pagas con tarjeta; se renueva solo y lo cancelas cuando quieras.');

        $this->withoutVite()->get('/legal/terminos')->assertOk()
            ->assertSee('Con tarjeta, la suscripción se renueva sola')
            ->assertSee('Con tarjeta puedes cambiar de plan, cambiar de tarjeta o cancelar cuando quieras desde «Mi plan»')
            ->assertDontSee('Con QR no hay cobro automático');
    }

    public function test_with_both_methods_qr_means_not_paying_the_next_period(): void
    {
        $this->configureLemonSqueezy();
        $this->configureLibelula();

        foreach (['/unete', '/precios'] as $path) {
            $this->withoutVite()->get($path)->assertOk()
                ->assertSee('Con tarjeta lo haces tú desde «Mi plan»')
                ->assertSee('Con QR no hay cobro automático: eliges otro plan en tu próximo pago')
                ->assertSee('lo coordinamos por WhatsApp')
                ->assertSee('Lo que ya publicaste sigue en línea.');
        }

        $this->withoutVite()->get('/legal/terminos')->assertOk()
            ->assertSee('para dejar el plan, no pagas el siguiente período')
            ->assertSee('Conservas el acceso hasta el final del período ya pagado')
            ->assertSee('No devolvemos meses parciales');
    }

    public function test_the_answer_is_the_same_text_on_the_prices_and_the_plans_page(): void
    {
        $this->configureLemonSqueezy();
        $this->configureLibelula();

        $question = '¿Puedo cambiar de plan o cancelarlo?';
        $prices = $this->faq($this->withoutVite()->get('/precios')->getContent(), $question);
        $plans = $this->faq($this->withoutVite()->get('/unete')->getContent(), $question);

        $this->assertNotSame('', $prices);
        $this->assertSame($prices, $plans);
    }

    public function test_the_terms_update_date_is_their_own(): void
    {
        $this->withoutVite()->get('/legal/terminos')->assertOk()->assertSee('Actualizada el 21 de octubre de 2026');
        $this->withoutVite()->get('/legal/privacidad')->assertOk()->assertSee('Actualizada el 23 de octubre de 2026');
        $this->withoutVite()->get('/legal/cookies')->assertOk()->assertSee('Actualizada el 23 de octubre de 2026');
    }

    public function test_the_english_pages_say_the_same_thing(): void
    {
        config(['payments.enabled' => false]);

        $this->withoutVite()->get('/en/join')->assertOk()
            ->assertSee('Can I change plans or cancel?')
            ->assertSee('Write to us on WhatsApp or at')
            ->assertSee('Plans for professionals');
    }

    public function test_every_site_page_has_the_same_menu_and_marks_the_open_one(): void
    {
        $menu = ['Precios', 'Planes', 'Guía'];

        foreach (['/precios' => 'Precios', '/unete' => 'Planes', '/guia-invitaciones-digitales' => 'Guía', '/legal/privacidad' => null, '/invitaciones-de-boda' => null, '/tarjetas-dia-del-amor' => null] as $path => $current) {
            $html = $this->withoutVite()->get($path)->assertOk()->getContent();
            $nav = Str::before(Str::after($html, 'aria-label="Secciones"'), '</nav>');

            foreach ($menu as $label) {
                $this->assertStringContainsString('>'.$label.'</a>', $nav, "{$path}: falta «{$label}» en el menú");
            }

            // Ya no hay anclas de la propia página en el menú
            $this->assertStringNotContainsString('href="#incluye"', $nav);
            $this->assertStringNotContainsString('href="#preguntas"', $nav);
            $this->assertStringNotContainsString('Qué incluye', $nav);

            $marked = preg_match_all('/aria-current="page"[^>]*>([^<]+)</', $nav, $found) ? $found[1] : [];
            $this->assertSame($current === null ? [] : [$current], $marked, "{$path}: página marcada");
        }
    }

    public function test_the_menu_links_go_to_their_pages_from_anywhere(): void
    {
        $html = $this->withoutVite()->get('/legal/terminos')->assertOk()->getContent();

        $this->assertStringContainsString('href="'.route('pricing').'"', $html);
        $this->assertStringContainsString('href="'.route('diy').'"', $html);
        $this->assertStringContainsString('href="'.route('guide').'"', $html);
        // «Plantillas» va a la portada desde las otras páginas
        $this->assertStringContainsString('href="'.route('home').'#plantillas"', $html);

        $this->withoutVite()->get('/en/pricing')->assertOk()
            ->assertSee('href="'.route('en.diy').'"', false)
            ->assertSee('>Plans</a>', false)
            ->assertSee('>Pricing</a>', false);
    }

    public function test_the_professional_offer_is_called_plans_everywhere(): void
    {
        foreach (['/', '/precios', '/unete', '/guia-invitaciones-digitales', '/legal/terminos', '/invitaciones-de-boda'] as $path) {
            $text = strip_tags($this->withoutVite()->get($path)->assertOk()->getContent());

            $this->assertStringNotContainsString('Únete', $text, "{$path} todavía dice «Únete»");
        }

        $this->withoutVite()->get('/unete')->assertOk()
            ->assertSee('Planes para profesionales')
            ->assertSee('¿Cuánto puedo ganar?')
            // «Hazlo tú» no es un plan: tiene su fila sin la palabra «Plan» y lleva a /precios
            ->assertSee('Hazlo tú (pago único)')
            ->assertDontSee('Plan Hazlo tú')
            ->assertSee('href="'.route('pricing').'#hazlo-tu"', false);

        $this->withoutVite()->get('/llms.txt')->assertOk()->assertDontSee('Únete');
        $this->withoutVite()->get('/en/llms.txt')->assertOk()->assertDontSee('Únete')->assertDontSee('Join');
    }
}
