<?php

namespace Tests\Feature;

use App\Modules\ModuleRegistry;
use App\Services\InvitationModuleService;
use Database\Seeders\BautizoCieloDemoSeeder;
use Database\Seeders\BodaJardinDemoSeeder;
use Database\Seeders\CumpleFiestaDemoSeeder;
use Database\Seeders\XvSofiaModuleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Cada módulo guarda en sus tablas y vuelve igual al leerlo: si algo se pierde en el camino, el
 * editor y las plantillas mostrarían otra cosa. No queda ningún dato en JSON.
 */
class StructuredModulesRoundTripTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public static function invitations(): array
    {
        return [
            'muestra XV años' => [fn () => (require database_path('seeders/showcase/xv-isabella.php'))['modules']],
            'muestra boda' => [fn () => (require database_path('seeders/showcase/boda-camila-andres.php'))['modules']],
            'muestra bautizo' => [fn () => (require database_path('seeders/showcase/bautizo-emilia.php'))['modules']],
            'muestra cumpleaños' => [fn () => (require database_path('seeders/showcase/cumple-daniela-30.php'))['modules']],
            'prueba XV' => [fn () => XvSofiaModuleData::all()],
            'prueba boda' => [fn () => BodaJardinDemoSeeder::modules()],
            'prueba bautizo' => [fn () => BautizoCieloDemoSeeder::modules()],
            'prueba cumpleaños' => [fn () => CumpleFiestaDemoSeeder::modules()],
        ];
    }

    #[DataProvider('invitations')]
    public function test_every_module_survives_the_trip_to_its_tables(\Closure $modules): void
    {
        $invitation = $this->createInvitation(['slug' => 'prueba-'.uniqid()]);
        $service = app(InvitationModuleService::class);

        $saved = $service->syncAllModules($invitation, $modules());
        $read = $service->resolveModules($invitation->fresh());

        foreach (app(ModuleRegistry::class)->codes() as $code) {
            // Todo lo guardado vuelve igual; la lectura puede sumar datos derivados (p. ej. «nombre» de la portada)
            $expected = $this->comparable($saved[$code] ?? []);
            $actual = $this->comparable($read[$code] ?? []);

            $this->assertSame($expected, $this->onlyKeysOf($expected, $actual), "El módulo «{$code}» cambió al pasar por sus tablas");
        }
    }

    public function test_the_modules_read_from_the_tables_without_any_json(): void
    {
        $data = require database_path('seeders/showcase/xv-isabella.php');
        $invitation = $this->createInvitation(['slug' => 'prueba-tablas']);
        app(InvitationModuleService::class)->syncAllModules($invitation, $data['modules']);

        $modules = app(InvitationModuleService::class)->storedModules($invitation->fresh());

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

    public function test_the_rows_land_in_their_tables(): void
    {
        $data = require database_path('seeders/showcase/boda-camila-andres.php');
        $invitation = $this->createInvitation(['slug' => 'prueba-filas']);
        app(InvitationModuleService::class)->syncAllModules($invitation, $data['modules']);

        $this->assertSame(1, $invitation->locations()->count());
        $this->assertGreaterThan(0, $invitation->featuredPeople()->count());
        $this->assertGreaterThan(0, $invitation->dressCodeItems()->count());
        $this->assertSame(2, $invitation->media()->count());
        $this->assertGreaterThan(0, $invitation->galleryImages()->where('collection', 'post_event')->count());
        $this->assertNotNull($invitation->theme()->first());
        $this->assertNotNull($invitation->hero()->first());
        $this->assertGreaterThan(0, $invitation->sections()->count());
        $this->assertGreaterThan(0, $invitation->features()->count());

        // Los grupos conservan su nombre y su orden
        $this->assertSame(
            array_keys(array_filter($data['modules']['destacados'], 'is_array')),
            $invitation->featuredPeople()->pluck('group')->unique()->values()->all()
        );

        // Los ejemplos de vestimenta son filas, no una lista en JSON
        $withExamples = collect($data['modules']['dress_code']['sugerencias'])->pluck('ejemplos')->flatten()->filter()->count();
        $this->assertDatabaseCount('invitation_dress_code_examples', $withExamples);
    }

    /** Punto 1 del plan: ninguna tabla de la aplicación guarda JSON. */
    public function test_no_application_table_has_a_json_column(): void
    {
        $jsonColumns = [];

        foreach (Schema::getTables() as $table) {
            // Tablas propias de Laravel (colas, caché, sesiones) quedan fuera: guardan cargas serializadas
            if (in_array($table['name'], ['jobs', 'job_batches', 'failed_jobs', 'cache', 'cache_locks', 'sessions', 'migrations'], true)) {
                continue;
            }

            foreach (Schema::getColumns($table['name']) as $column) {
                if (in_array(strtolower($column['type_name']), ['json', 'jsonb'], true)) {
                    $jsonColumns[] = "{$table['name']}.{$column['name']}";
                }
            }
        }

        $this->assertSame([], $jsonColumns, 'Hay columnas JSON: '.implode(', ', $jsonColumns));
        $this->assertFalse(Schema::hasTable('invitation_data'));
        $this->assertFalse(Schema::hasTable('invitation_settings'));
    }

    /** Recorta $actual a las claves que tiene $expected, en profundidad (las listas se comparan enteras). */
    private function onlyKeysOf(mixed $expected, mixed $actual): mixed
    {
        if (! is_array($expected) || ! is_array($actual)) {
            return $actual;
        }

        if (array_is_list($expected)) {
            return array_is_list($actual) && count($actual) === count($expected)
                ? array_map(fn ($item, $index) => $this->onlyKeysOf($item, $actual[$index]), $expected, array_keys($expected))
                : $actual;
        }

        $trimmed = [];

        foreach ($expected as $key => $item) {
            if (array_key_exists($key, $actual)) {
                $trimmed[$key] = $this->onlyKeysOf($item, $actual[$key]);
            }
        }

        return $trimmed;
    }

    /**
     * Lo que importa es el contenido: se quitan los vacíos (un texto vacío no se guarda), se
     * recortan los espacios, se ordenan las claves y los números se comparan como número.
     */
    private function comparable(mixed $value): mixed
    {
        if (is_object($value)) {
            $value = (array) $value;
        }

        if (is_array($value)) {
            $value = array_map(fn ($item) => $this->comparable($item), $value);
            $isList = array_is_list($value);
            $value = array_filter($value, fn ($item) => $item !== null && $item !== '' && $item !== []);

            if ($isList) {
                return array_values($value);
            }

            ksort($value);

            return $value;
        }

        // Los textos se guardan sin espacios sobrantes
        if (is_string($value)) {
            return trim($value);
        }

        return is_numeric($value) ? (float) $value : $value;
    }
}
