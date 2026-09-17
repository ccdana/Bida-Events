<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Punto 30: el respaldo se crea y se puede restaurar. Usa una base SQLite en archivo propia
 * (sin RefreshDatabase, que trabaja dentro de una transacción) y una carpeta temporal,
 * para no depender de pg_dump ni tocar storage/app/backups de la instalación.
 */
class BackupRestoreTest extends TestCase
{
    private string $workspace;

    private string $previousConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->workspace = str_replace('\\', '/', sys_get_temp_dir()).'/bida-respaldo-'.uniqid();
        File::ensureDirectoryExists($this->workspace);

        $database = $this->workspace.'/base.sqlite';
        touch($database);

        $this->previousConnection = config('database.default');
        config([
            'operations.backups.path' => $this->workspace.'/respaldos',
            'database.connections.respaldo' => ['driver' => 'sqlite', 'database' => $database, 'prefix' => '', 'foreign_key_constraints' => true],
            'database.default' => 'respaldo',
        ]);
    }

    protected function tearDown(): void
    {
        DB::purge('respaldo');
        DB::purge('restauracion_prueba');
        config(['database.default' => $this->previousConnection]);
        File::deleteDirectory($this->workspace);

        parent::tearDown();
    }

    public function test_a_backup_is_created_and_restores_into_a_readable_copy(): void
    {
        DB::statement('CREATE TABLE invitados (id INTEGER PRIMARY KEY, nombre TEXT)');
        DB::table('invitados')->insert([['nombre' => 'Familia Quispe'], ['nombre' => 'Familia Mamani']]);

        $this->artisan('bida:respaldo')->assertSuccessful();

        $dumps = glob($this->workspace.'/respaldos/base-*.sql.gz');
        $this->assertCount(1, $dumps);
        $this->assertFileExists(str_replace(['base-', '.sql.gz'], ['medios-', '.json'], $dumps[0]));

        $this->artisan('bida:probar-respaldo')
            ->expectsOutputToContain('se restauró bien')
            ->assertSuccessful();

        $record = json_decode(File::get($this->workspace.'/respaldos/ultima-prueba.json'), true);
        $this->assertTrue($record['passed']);
        $this->assertSame(1, $record['tables']);
        $this->assertSame(2, $record['rows']);
    }

    public function test_the_restore_test_fails_loudly_when_the_dump_is_broken(): void
    {
        File::ensureDirectoryExists($this->workspace.'/respaldos');
        $broken = $this->workspace.'/respaldos/base-2026-01-01_000000.sql.gz';
        File::put($broken, gzencode('esto no es una base de datos'));

        $this->artisan('bida:probar-respaldo', ['archivo' => $broken])->assertFailed();

        $record = json_decode(File::get($this->workspace.'/respaldos/ultima-prueba.json'), true);
        $this->assertFalse($record['passed']);
    }
}
