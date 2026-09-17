<?php

namespace Tests\Feature;

use App\Services\InvitationModuleService;
use App\Support\InvitationDefaults;
use App\Support\InvitationTemplates;
use Database\Seeders\BautizoCieloDemoSeeder;
use Database\Seeders\BodaJardinDemoSeeder;
use Database\Seeders\CumpleFiestaDemoSeeder;
use Database\Seeders\XvSofiaModuleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Punto 29, regresión mínima: las cuatro plantillas se arman con todos los módulos encendidos,
 * tanto recién creadas (vacías) como con la invitación de prueba completa, en el enlace
 * general y en el personal. Atrapa vistas que revientan con un dato faltante.
 */
class TemplateRenderMatrixTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public static function templates(): array
    {
        return [
            'XV años' => [InvitationTemplates::XV_PREMIUM, 'inv-xv', fn () => XvSofiaModuleData::all()],
            'boda' => [InvitationTemplates::BODA_JARDIN, 'inv-boda', fn () => BodaJardinDemoSeeder::modules()],
            'bautizo' => [InvitationTemplates::BAUTIZO_CIELO, 'inv-bautizo', fn () => BautizoCieloDemoSeeder::modules()],
            'cumpleaños' => [InvitationTemplates::CUMPLE_FIESTA, 'inv-cumple', fn () => CumpleFiestaDemoSeeder::modules()],
        ];
    }

    #[DataProvider('templates')]
    public function test_the_template_renders_with_every_module_on_and_no_content(string $template, string $bodyClass): void
    {
        $modules = InvitationDefaults::emptyModules();
        $modules['config']['template'] = $template;
        // Todo encendido: es el caso en que más parciales se arman sin datos
        $modules['config']['modulos'] = array_fill_keys(array_keys($modules['config']['modulos']), true);

        $this->assertRendersBothLinks($template, $bodyClass, $modules);
    }

    #[DataProvider('templates')]
    public function test_the_template_renders_with_complete_content(string $template, string $bodyClass, \Closure $modules): void
    {
        $html = $this->assertRendersBothLinks($template, $bodyClass, $modules());

        foreach (['id="itinerario"', 'id="ubicacion"', 'id="galeria"', 'id="rsvp"'] as $section) {
            $this->assertStringContainsString($section, $html, "Falta {$section} en {$template}");
        }
    }

    /** Arma el enlace general y el personal; devuelve el HTML del personal. */
    private function assertRendersBothLinks(string $template, string $bodyClass, array $modules): string
    {
        $invitation = $this->createInvitation([
            'slug' => 'prueba-'.Str::lower(Str::random(6)),
            'template' => $template,
        ]);
        app(InvitationModuleService::class)->syncAllModules($invitation, $modules);
        $guest = $invitation->guests()->create(['name' => 'Familia Quispe', 'passes_allocated' => 2]);

        $general = $this->withoutVite()->get(route('invitation.show', $invitation->slug));
        $general->assertOk()->assertSee("inv-page {$bodyClass}", false);

        $personal = $this->withoutVite()->get(route('invitation.guest', ['slug' => $invitation->slug, 'token' => $guest->qr_code_token]));
        $personal->assertOk()->assertSee('Familia Quispe');

        $html = $personal->getContent();

        foreach ([$general->getContent(), $html] as $page) {
            $this->assertStringNotContainsString('Undefined', $page);
            $this->assertStringNotContainsString('ErrorException', $page);
        }

        return $html;
    }
}
