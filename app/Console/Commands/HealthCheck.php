<?php

namespace App\Console\Commands;

use App\Models\InvitationExport;
use App\Services\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Revisión cada hora (routes/console.php): trabajos fallidos, cola detenida, exportaciones
 * atascadas, respaldos y disco. Si algo está mal lo deja en storage/logs/operations-*.log y,
 * con OPERATIONS_ALERT_EMAIL, manda un correo.
 */
class HealthCheck extends Command
{
    protected $signature = 'bida:salud {--sin-correo : Solo muestra el resultado}';

    protected $description = 'Revisa colas, respaldos y disco, y avisa si algo necesita atención';

    public function handle(BackupService $backups): int
    {
        $checks = [
            $this->failedJobs(),
            $this->stalledQueue(),
            $this->stuckExports(),
            $this->latestBackup($backups),
            $this->latestRestoreTest($backups),
            $this->freeDisk(),
        ];

        $this->table(['', 'Revisión', 'Resultado'], array_map(
            fn (array $check) => [$check['ok'] ? 'OK' : 'FALLA', $check['label'], $check['detail']],
            $checks,
        ));

        $problems = array_values(array_filter($checks, fn (array $check) => ! $check['ok']));

        if ($problems === []) {
            return self::SUCCESS;
        }

        $summary = implode("\n", array_map(fn (array $check) => "- {$check['label']}: {$check['detail']}", $problems));
        Log::channel('operations')->critical('bida:salud encontró problemas', ['problems' => $problems]);

        $email = config('operations.alerts.email');

        if ($email && ! $this->option('sin-correo')) {
            rescue(fn () => Mail::raw(
                'Revisión de '.config('app.name').' ('.config('app.url').")\n\n{$summary}\n\nDetalle en storage/logs/operations.log",
                fn ($message) => $message->to($email)->subject('['.config('app.name').'] '.count($problems).' problema(s) en la revisión'),
            ));
        }

        return self::FAILURE;
    }

    private function failedJobs(): array
    {
        $threshold = (int) config('operations.alerts.failed_jobs', 1);
        $recent = $this->safeCount(fn () => DB::table('failed_jobs')->where('failed_at', '>=', now()->subDay())->count());

        return $this->check('Trabajos fallidos (24 h)', $recent !== null && $recent < $threshold,
            $recent === null ? 'no se pudo leer failed_jobs' : "{$recent} (se avisa desde {$threshold})");
    }

    private function stalledQueue(): array
    {
        if (config('queue.default') !== 'database') {
            return $this->check('Cola', true, 'conexión '.config('queue.default').': no se revisa aquí');
        }

        $minutes = (int) config('operations.alerts.pending_job_minutes', 30);
        $oldest = $this->safeCount(fn () => DB::table('jobs')->whereNull('reserved_at')->min('available_at'));

        if ($oldest === null) {
            return $this->check('Cola', true, 'sin trabajos pendientes');
        }

        $waiting = (int) floor((now()->getTimestamp() - $oldest) / 60);

        return $this->check('Cola', $waiting < $minutes, $waiting < $minutes
            ? "el más viejo espera {$waiting} min"
            : "un trabajo espera {$waiting} min: ¿está corriendo queue:work?");
    }

    private function stuckExports(): array
    {
        $stuck = $this->safeCount(fn () => InvitationExport::where('status', InvitationExport::PENDING)
            ->where('created_at', '<', now()->subMinutes(15))
            ->count());

        return $this->check('Exportaciones atascadas', $stuck === 0, $stuck === null ? 'no se pudo leer' : "{$stuck} pendientes hace más de 15 min");
    }

    private function latestBackup(BackupService $backups): array
    {
        $age = $backups->latestDumpAge();
        $maxHours = (int) config('operations.alerts.backup_max_age_hours', 26);

        if (! $age) {
            return $this->check('Último respaldo', false, 'no hay ninguno');
        }

        $hours = (int) $age->diffInHours(now());

        return $this->check('Último respaldo', $hours < $maxHours, "hace {$hours} h ({$age->format('Y-m-d H:i')})");
    }

    private function latestRestoreTest(BackupService $backups): array
    {
        $file = $backups->directory().'/ultima-prueba.json';
        $result = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;

        if (! is_array($result)) {
            return $this->check('Prueba de restauración', false, 'nunca se probó un respaldo');
        }

        $testedAt = Carbon::parse($result['tested_at']);
        $days = (int) $testedAt->diffInDays(now());

        if (! $result['passed']) {
            return $this->check('Prueba de restauración', false, "falló el {$testedAt->format('Y-m-d')}: {$result['error']}");
        }

        return $this->check('Prueba de restauración', $days <= 8, "correcta hace {$days} días");
    }

    private function freeDisk(): array
    {
        $total = @disk_total_space(storage_path());
        $free = @disk_free_space(storage_path());

        if (! $total || $free === false) {
            return $this->check('Disco', true, 'no se pudo medir');
        }

        $percent = (int) round($free / $total * 100);
        $minimum = (int) config('operations.alerts.min_free_disk_percent', 10);

        return $this->check('Disco', $percent >= $minimum, "{$percent} % libre");
    }

    private function check(string $label, bool $ok, string $detail): array
    {
        return ['label' => $label, 'ok' => $ok, 'detail' => $detail];
    }

    private function safeCount(callable $query): ?int
    {
        try {
            $value = $query();

            return $value === null ? null : (int) $value;
        } catch (Throwable) {
            return null;
        }
    }
}
