<?php

namespace Tests\Feature;

use App\Services\InvitationModuleService;
use App\Support\ColorContrast;
use App\Support\InvitationTemplates;
use Database\Seeders\XvSofiaModuleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Puntos 22 y 23 del mapa: la invitación se lee sin JavaScript y sus colores tienen
 * contraste suficiente. Las dos cosas se rompen sin querer al cambiar estilos, así que
 * quedan fijadas aquí.
 */
class AccessibleInvitationTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public function test_the_essential_content_is_in_the_html_and_does_not_depend_on_javascript(): void
    {
        $invitation = $this->createInvitation(['slug' => 'xv-accesible']);
        app(InvitationModuleService::class)->syncAllModules($invitation, XvSofiaModuleData::all());

        $response = $this->withoutVite()
            ->get(route('invitation.show', $invitation->slug))
            ->assertOk()
            // La clase la quita el primer script: sin JavaScript, base.css muestra todo plano
            ->assertSee('<html lang="es" class="no-js">', false)
            ->assertSee("root.classList.remove('no-js')", false)
            // Primer elemento enfocable: se llega al contenido sin recorrer el menú
            ->assertSee('<a class="inv-skip" href="#contenido">Saltar al contenido</a>', false);

        $html = $response->getContent();

        // Cuándo, dónde y cómo: todo servido por el servidor, no pintado por Alpine
        $this->assertStringContainsString('Salón Imperial La Paz', $html);
        $this->assertStringContainsString('Ceremonia de Velas', $html);
        $this->assertStringContainsString('id="itinerario"', $html);
        $this->assertStringContainsString('id="ubicacion"', $html);

        // Lo que de verdad necesita JavaScript avisa en vez de quedarse mudo
        $this->assertStringContainsString('Para sugerir una canción necesitas activar JavaScript', $html);
        $this->assertStringContainsString('Para votar en las encuestas necesitas activar JavaScript', $html);
    }

    public function test_the_bank_details_are_readable_without_javascript(): void
    {
        $invitation = $this->createInvitation(['slug' => 'xv-regalos']);
        app(InvitationModuleService::class)->syncAllModules($invitation, XvSofiaModuleData::all());

        $html = $this->withoutVite()
            ->get(route('invitation.show', $invitation->slug))
            ->assertOk()
            ->getContent();

        // Los datos viven en un <template x-teleport>, que sin Alpine nunca se pinta:
        // la copia dentro de <noscript> es la que salva al invitado
        preg_match_all('/<noscript>(.*?)<\/noscript>/s', $html, $matches);
        $noscript = implode('', $matches[1]);

        $this->assertStringContainsString('Banco Nacional de Bolivia', $noscript);
        $this->assertStringContainsString('1500123456789', $noscript);
        $this->assertStringContainsString('Código QR para transferir', $noscript);
    }

    public function test_the_menu_returns_the_focus_and_traps_the_tab_key(): void
    {
        $invitation = $this->createInvitation();
        app(InvitationModuleService::class)->syncAllModules($invitation, XvSofiaModuleData::all());

        $this->withoutVite()
            ->get(route('invitation.show', $invitation->slug))
            ->assertOk()
            ->assertSee('@keydown.tab="trapFocus($event)"', false)
            ->assertSee('x-ref="toggle"', false)
            // Seguir un enlace no devuelve el foco al botón: el destino es la sección
            ->assertSee('@click="close(false)"', false);
    }

    /**
     * El invitado no lee los colores del cliente tal cual: los tonos apagados salen de
     * mezclarlos. Cada plantilla tiene que llegar al mínimo de WCAG AA con esas mezclas.
     */
    #[DataProvider('templates')]
    public function test_every_theme_palette_reaches_the_minimum_contrast(string $template): void
    {
        $palette = InvitationTemplates::palette($template);

        foreach (ColorContrast::audit($palette) as $check) {
            $this->assertTrue(
                $check['passes'],
                sprintf(
                    '%s: «%s» queda en %.2f:1 y necesita %.1f:1',
                    InvitationTemplates::all()[$template]['label'],
                    $check['label'],
                    $check['ratio'],
                    $check['min'],
                )
            );
        }
    }

    public static function templates(): array
    {
        return [
            'XV años' => [InvitationTemplates::XV_PREMIUM],
            'boda' => [InvitationTemplates::BODA_JARDIN],
            'bautizo' => [InvitationTemplates::BAUTIZO_CIELO],
            'cumpleaños' => [InvitationTemplates::CUMPLE_FIESTA],
        ];
    }

    public function test_a_photo_description_becomes_the_alt_text(): void
    {
        $invitation = $this->createInvitation(['slug' => 'xv-con-alt']);

        $modules = XvSofiaModuleData::all();
        $modules['galeria']['fotos'] = [
            ['url' => 'https://ejemplo.test/foto-uno.jpg', 'alt' => 'Sofía con su vestido en el jardín'],
            'https://ejemplo.test/foto-dos.jpg',
        ];

        app(InvitationModuleService::class)->syncAllModules($invitation, $modules);

        $this->withoutVite()
            ->get(route('invitation.show', $invitation->slug))
            ->assertOk()
            ->assertSee('alt="Sofía con su vestido en el jardín"', false)
            // Sin descripción se conserva el texto de respaldo
            ->assertSee('alt="Foto 2 de 2"', false);
    }
}
