<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Un respaldo que nunca se restauró no está probado. Este comando carga el último volcado en una
 * base temporal ({base}_restauracion_prueba), compara las filas con la base real y la borra.
 * El resultado queda en storage/app/backups/ultima-prueba.json, que revisa bida:salud.
 */
class TestBackupRestore extends Command
{
    protected $signature = 'bida:probar-respaldo {archivo? : Volcado .sql.gz; por defecto el más reciente}';

    protected $description = 'Restaura el último respaldo en una base temporal y verifica que se pueda leer';

    public function handle(BackupService $backups): int
    {
        $dump = $this->argument('archivo') ?: $backups->latestDump();

        if (! $dump || ! is_file($dump)) {
            $this->components->error('No hay respaldos para probar. Corre primero php artisan bida:respaldo.');

            return self::FAILURE;
        }

        try {
            $result = $backups->testRestore($dump);
        } catch (Throwable $exception) {
            $this->record($backups, $dump, false, $exception->getMessage());
            Log::channel('operations')->critical('Prueba de restauración fallida', ['file' => basename($dump), 'error' => $exception->getMessage()]);
            $this->components->error('La restauración falló: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->record($backups, $dump, true, null, $result);
        Log::channel('operations')->info('Prueba de restauración correcta', ['file' => basename($dump)] + $result);

        $this->components->info('El respaldo '.basename($dump).' se restauró bien.');
        $this->components->twoColumnDetail('Tablas', (string) $result['tables']);
        $this->components->twoColumnDetail('Filas restauradas', (string) $result['rows']);

        if ($result['differences']) {
            $this->newLine();
            $this->line('  Tablas que cambiaron desde el respaldo (normal si hubo actividad después):');
            $this->table(['Tabla', 'Ahora', 'En el respaldo'], collect($result['differences'])
                ->map(fn (array $counts, string $table) => [$table, $counts['live'], $counts['restored']])
                ->values()
                ->all());
        }

        return self::SUCCESS;
    }

    private function record(BackupService $backups, string $dump, bool $passed, ?string $error, array $result = []): void
    {
        File::ensureDirectoryExists($backups->directory());
        File::put($backups->directory().'/ultima-prueba.json', json_encode([
            'tested_at' => now()->toIso8601String(),
            'file' => basename($dump),
            'passed' => $passed,
            'error' => $error,
            'tables' => $result['tables'] ?? null,
            'rows' => $result['rows'] ?? null,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
