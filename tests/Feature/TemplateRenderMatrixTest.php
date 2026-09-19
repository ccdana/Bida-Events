<?php

namespace Tests\Feature;

use App\EventProfiles\EventProfiles;
use App\Modules\Module;
use App\Services\InvitationModuleService;
use App\Support\InvitationDefaults;
use App\Support\InvitationTemplates;
use Database\Seeders\BautizoCieloDemoSeeder;
use Database\Seeders\BodaJardinDemoSeeder;
use Database\Seeders\CumpleFiestaDemoSeeder;
use Database\Seeders\ShowcaseInvitationsSeeder;
use Database\Seeders\XvSofiaModuleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Punto 29, regresión mínima: cada plantilla del catálogo se arma con todos los módulos encendidos,
 * tanto recién creada (vacía) como con su contenido de prueba completo, en el enlace
 * general y en el personal. Atrapa vistas que revientan con un dato faltante.
 *
 * Las plantillas salen de InvitationTemplates::all(): una plantilla nueva entra sola a la matriz
 * y la prueba falla hasta que tenga contenido de prueba en completeContent().
 */
class TemplateRenderMatrixTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public static function templates(): array
    {
        // La clase del <body> es inv-{evento}: inv-xv, inv-boda, inv-amor…
        return collect(InvitationTemplates::all())
            ->mapWithKeys(fn (array $entry, string $template) => [$entry['label'] => [$template, 'inv-'.$entry['event']]])
            ->all();
    }

    /** Contenido completo de prueba por evento. Una temporada nueva suma aquí su muestra. */
    private static function completeContent(string $template): array
    {
        return match (InvitationTemplates::all()[$template]['event']) {
            'xv' => XvSofiaModuleData::all(),
            'boda' => BodaJardinDemoSeeder::modules(),
            'bautizo' => BautizoCieloDemoSeeder::modules(),
            'cumple' => CumpleFiestaDemoSeeder::modules(),
            'amor' => ShowcaseInvitationsSeeder::data('tarjeta-ana-luis')['modules'],
            'historia' => ShowcaseInvitationsSeeder::data('historia-ana-luis')['modules'],
        };
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
    public function test_the_template_renders_with_complete_content(string $template, string $bodyClass): void
    {
        $html = $this->assertRendersBothLinks($template, $bodyClass, self::completeContent($template));

        $profile = app(EventProfiles::class)->forTemplate($template);

        // Lo que se ve depende del perfil: una tarjeta no tiene confirmación ni itinerario
        $expected = $profile->kind() === Module::KIND_CARD
            ? ['id="dedicatoria"', 'id="galeria"', 'id="respuesta"']
            : ['id="itinerario"', 'id="ubicacion"', 'id="galeria"', 'id="rsvp"'];

        foreach ($expected as $section) {
            $this->assertStringContainsString($section, $html, "Falta {$section} en {$template}");
        }

        foreach (['itinerario', 'ubicacion', 'rsvp', 'dress_code', 'regalos'] as $module) {
            if (! in_array($module, $profile->modules(), true)) {
                $this->assertStringNotContainsString('id="'.$module.'"', $html, "{$template} no debería mostrar {$module}");
            }
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
        // En una tarjeta con dedicatoria manda el «para»; sin ella, el nombre del enlace personal
        $personal->assertOk()->assertSee(data_get($modules, 'dedicatoria.para') ?: 'Familia Quispe');

        $html = $personal->getContent();

        foreach ([$general->getContent(), $html] as $page) {
            $this->assertStringNotContainsString('Undefined', $page);
            $this->assertStringNotContainsString('ErrorException', $page);
        }

        return $html;
    }
}
