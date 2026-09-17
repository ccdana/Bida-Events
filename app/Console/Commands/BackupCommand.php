<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Throwable;

/**
 * Respaldo nocturno (routes/console.php): base de datos, medios locales y manifiesto de Cloudinary.
 * Se guarda en storage/app/backups; copiar esa carpeta fuera del servidor es parte del procedimiento
 * (docs/operacion.md).
 */
class BackupCommand extends Command
{
    protected $signature = 'bida:respaldo {--sin-medios : Solo la base de datos y el manifiesto}';

    protected $description = 'Respalda la base de datos y los medios, y borra los respaldos vencidos';

    public function handle(BackupService $backups): int
    {
        try {
            $result = $backups->run(withMedia: ! $this->option('sin-medios'));
        } catch (Throwable $exception) {
            Log::channel('operations')->critical('Respaldo fallido', ['error' => $exception->getMessage()]);
            $this->components->error('El respaldo falló: '.$exception->getMessage());

            return self::FAILURE;
        }

        $deleted = $backups->prune();

        Log::channel('operations')->info('Respaldo creado', [
            'database' => basename($result['database']),
            'media' => $result['media'] ? basename($result['media']) : null,
            'size' => $result['size'],
            'pruned' => $deleted,
        ]);

        $this->components->twoColumnDetail('Base de datos', basename($result['database']));
        $this->components->twoColumnDetail('Medios locales', $result['media'] ? basename($result['media']) : 'no hay');
        $this->components->twoColumnDetail('Manifiesto de Cloudinary', basename($result['manifest']));
        $this->components->twoColumnDetail('Tamaño', Number::fileSize($result['size']));
        $this->components->twoColumnDetail('Respaldos vencidos borrados', (string) $deleted);

        return self::SUCCESS;
    }
}
