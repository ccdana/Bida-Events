<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Lo que se toca en el sitio público y en el ingreso mide al menos 44 px en celulares y tablets. El tamaño real
 * lo da el CSS (solo en pantallas táctiles o estrechas); aquí se comprueba que las piezas lo piden.
 */
class TouchTargetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_touch_rules_only_apply_to_phones_tablets_and_touch_screens(): void
    {
        $css = file_get_contents(resource_path('css/site/site.css'));

        $this->assertSame(1, preg_match('/@media \(pointer: coarse\), \(max-width: 1023px\) \{(?<rules>.*?)\n\}\n/s', $css, $block));

        foreach (['.site-touch-y', '.site-link-arrow', '.site-foot__mail', '.site-touch-row', '.site-touch {', 'button.site-cycle__option', 'body .theme-toggle'] as $selector) {
            $this->assertStringContainsString($selector, $block['rules'], "{$selector} debe estar dentro del bloque táctil");
        }

        $this->assertStringContainsString('min-height: 2.75rem', $block['rules']);
        // Fuera del bloque no hay reglas de toque: el computador conserva su densidad
        $outside = str_replace($block[0], '', $css);
        $this->assertStringNotContainsString('.site-touch-y', $outside);
        $this->assertStringNotContainsString('.site-touch-row', $outside);
    }

    public function test_the_footer_and_the_sign_in_pages_ask_for_touch_areas(): void
    {
        // Idioma del pie
        $this->withoutVite()->get('/')->assertOk()->assertSee('hover:text-site-ink site-touch', false);

        // Migas de pan de la guía, las legales y las páginas por evento
        foreach (['/guia-invitaciones-digitales', '/legal/privacidad', '/invitaciones-de-boda'] as $path) {
            $this->withoutVite()->get($path)->assertOk()->assertSee('site-nav-link site-touch-y', false);
        }

        // Ingreso: idioma, «¿La olvidaste?» y la casilla de recordar con su texto
        $this->withoutVite()->get('/login')->assertOk()
            ->assertSee('hover:text-site-ink site-touch', false)
            ->assertSee('site-touch-y text-sm text-site-muted', false)
            ->assertSee('site-touch-row', false);

        $this->withoutVite()->get('/registro')->assertOk()->assertSee('hover:text-site-ink site-touch', false);
    }

    public function test_the_language_switch_is_only_enlarged_where_asked(): void
    {
        // En la cabecera de escritorio no lleva la clase: es el mismo selector compacto de siempre
        $html = $this->withoutVite()->get('/precios')->assertOk()->getContent();
        $desktop = \Illuminate\Support\Str::before(\Illuminate\Support\Str::after($html, 'class="hidden items-center gap-5 lg:flex"'), '</nav>');

        $this->assertStringContainsString('hreflang="en"', $desktop);
        $this->assertStringNotContainsString('site-touch', $desktop);
    }
}
