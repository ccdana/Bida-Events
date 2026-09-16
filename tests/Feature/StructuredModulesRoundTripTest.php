<?php

namespace Tests\Feature;

use App\Services\InvitationModuleService;
use App\Services\InvitationStructuredDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Los módulos que ahora viven en tablas deben volver exactamente iguales al leerlos: si algo se
 * pierde en el camino, el editor y las plantillas mostrarían otra cosa.
 */
class StructuredModulesRoundTripTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public static function showcaseProvider(): array
    {
        return [
            'XV años' => ['xv-isabella'],
            'boda' => ['boda-camila-andres'],
            'bautizo' => ['bautizo-emilia'],
            'cumpleaños' => ['cumple-daniela-30'],
        ];
    }

    #[DataProvider('showcaseProvider')]
    public function test_the_showcase_modules_survive_the_trip_to_tables(string $slug): void
    {
        $data = require database_path("seeders/showcase/{$slug}.php");
        $invitation = $this->createInvitation(['slug' => 'prueba-'.$slug]);
        $service = app(InvitationModuleService::class);
        $structured = app(InvitationStructuredDataService::class);

        $normalized = $service->syncAllModules($invitation, $data['modules']);

        // 1. La verificación del comando de migración no encuentra diferencias
        $this->assertSame([], $structured->verify($invitation->fresh(), $normalized));

        // 2. Lo que se lee desde las tablas es igual a lo que se guardó
        $hydrated = $structured->hydrate($invitation->fresh(), $normalized);

        foreach (['ubicacion', 'destacados', 'dress_code', 'regalos', 'musica', 'video', 'post_evento'] as $module) {
            $this->assertSame(
                $this->sorted($normalized[$module] ?? []),
                $this->sorted($hydrated[$module] ?? []),
                "El módulo {$module} cambió al pasar por las tablas"
            );
        }
    }

    public function test_tables_are_the_source_even_without_the_json(): void
    {
        $data = require database_path('seeders/showcase/xv-isabella.php');
        $invitation = $this->createInvitation(['slug' => 'prueba-sin-json']);
        $service = app(InvitationModuleService::class);
        $service->syncAllModules($invitation, $data['modules']);

        // Se borra el JSON: la invitación debe seguir mostrando lo mismo desde las tablas
        $invitation->modulesData()->delete();
        $invitation->clearModulesCache();

        $modules = $service->storedModules($invitation->fresh());

        $this->assertSame('Salón Gran Cristal', $modules['ubicacion']['nombre_lugar']);
        $this->assertSame('Sebastián Rojas', $modules['destacados']['chambelanes'][0]['nombre']);
        $this->assertSame('Padrinos de honor', $modules['destacados']['padrinos'][0]['rol']);
        $this->assertSame('Sr. Gonzalo y Sra. Patricia Mendoza', $modules['destacados']['padrinos'][0]['nombres']);
        $this->assertSame('Vestido largo de gala', $modules['dress_code']['sugerencias'][0]['titulo']);
        $this->assertSame('Esmeralda', $modules['dress_code']['colores_permitidos'][0]['nombre']);
        $this->assertContains('Blanco total', $modules['dress_code']['evitar']);
        $this->assertSame('Mi canción', $modules['musica']['titulo']);
        $this->assertSame('Faltan pocos días', $modules['video']['titulo']);
        $this->assertCount(3, $modules['post_evento']['fotos']);
    }

    public function test_the_new_tables_receive_the_rows(): void
    {
        $data = require database_path('seeders/showcase/boda-camila-andres.php');
        $invitation = $this->createInvitation(['slug' => 'prueba-filas']);
        app(InvitationModuleService::class)->syncAllModules($invitation, $data['modules']);

        $this->assertSame(1, $invitation->locations()->count());
        $this->assertGreaterThan(0, $invitation->featuredPeople()->count());
        $this->assertGreaterThan(0, $invitation->dressCodeItems()->count());
        $this->assertSame(2, $invitation->media()->count());
        $this->assertGreaterThan(0, $invitation->galleryImages()->where('collection', 'post_event')->count());

        // Los grupos conservan su nombre y su orden
        $this->assertSame(
            array_keys(array_filter($data['modules']['destacados'], 'is_array')),
            $invitation->featuredPeople()->pluck('group')->unique()->values()->all()
        );
    }

    /** Ordena las claves en profundidad: el contenido debe ser igual aunque el orden de claves cambie. */
    private function sorted(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $value = array_map(fn ($item) => $this->sorted($item), $value);

        if (! array_is_list($value)) {
            ksort($value);
        }

        return $value;
    }
}
