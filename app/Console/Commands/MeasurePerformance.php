<?php

namespace App\Console\Commands;

use App\Models\Invitation;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Medición para comparar antes y después de optimizar: tiempo, consultas, memoria y peso del HTML
 * de las pantallas públicas. Pensado para correr en local contra datos de muestra; sirve para que
 * "quedó más rápido" sea un número y no una impresión.
 */
class MeasurePerformance extends Command
{
    protected $signature = 'bida:medir
                            {--repeticiones=5 : Cuántas veces se pide cada pantalla}
                            {--guardar : Escribe el resultado en docs/rendimiento.md}';

    protected $description = 'Mide tiempo, consultas, memoria y peso del HTML de las pantallas públicas';

    public function handle(Kernel $kernel): int
    {
        $repetitions = max(1, (int) $this->option('repeticiones'));
        $invitation = Invitation::query()->published()->orderBy('id')->first();

        if (! $invitation) {
            $this->components->error('No hay invitaciones publicadas: corre primero php artisan db:seed.');

            return self::FAILURE;
        }

        $targets = [
            'Portada' => '/',
            'Invitación' => "/p/{$invitation->slug}",
            'Muestra' => "/muestra/{$invitation->slug}",
        ];

        $rows = [];

        foreach ($targets as $label => $uri) {
            $samples = [];

            for ($i = 0; $i < $repetitions; $i++) {
                $samples[] = $this->measure($kernel, $uri);
            }

            $rows[] = [
                $label,
                $uri,
                $this->median(array_column($samples, 'ms')).' ms',
                $this->percentile95(array_column($samples, 'ms')).' ms',
                $this->median(array_column($samples, 'queries')),
                $this->median(array_column($samples, 'memory')).' KB',
                $this->median(array_column($samples, 'size')).' KB',
            ];
        }

        $headers = ['Pantalla', 'Ruta', 'Tiempo P50', 'Tiempo P95', 'Consultas', 'Memoria', 'HTML'];
        $this->table($headers, $rows);

        if ($this->option('guardar')) {
            $this->save($headers, $rows, $repetitions);
        }

        $this->components->info('Medido con '.$repetitions.' repeticiones por pantalla. La primera petición siempre es más lenta: se descarta.');

        return self::SUCCESS;
    }

    /** @return array{ms: float, queries: int, memory: float, size: float} */
    private function measure(Kernel $kernel, string $uri): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $memoryBefore = memory_get_usage();
        $start = hrtime(true);

        $response = $kernel->handle(Request::create($uri, 'GET'));

        $ms = (hrtime(true) - $start) / 1_000_000;
        $memory = (memory_get_usage() - $memoryBefore) / 1024;
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        return [
            'ms' => round($ms, 1),
            'queries' => $queries,
            'memory' => round(max($memory, 0), 1),
            'size' => round(strlen((string) $response->getContent()) / 1024, 1),
        ];
    }

    private function median(array $values): float|int
    {
        if ($values === []) {
            return 0;
        }

        sort($values);
        $middle = (int) floor((count($values) - 1) / 2);

        return count($values) % 2 ? $values[$middle] : round(($values[$middle] + $values[$middle + 1]) / 2, 1);
    }

    private function percentile95(array $values): float|int
    {
        if ($values === []) {
            return 0;
        }

        sort($values);

        return $values[(int) ceil(0.95 * count($values)) - 1];
    }

    private function save(array $headers, array $rows, int $repetitions): void
    {
        $path = base_path('docs/rendimiento.md');

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $lines = [
            '# Medición de rendimiento',
            '',
            'Generado con `php artisan bida:medir --guardar` ('.$repetitions.' repeticiones por pantalla).',
            'Fecha: '.now()->format('Y-m-d H:i').'. Entorno: '.app()->environment().'.',
            '',
            '| '.implode(' | ', $headers).' |',
            '| '.implode(' | ', array_fill(0, count($headers), '---')).' |',
        ];

        foreach ($rows as $row) {
            $lines[] = '| '.implode(' | ', $row).' |';
        }

        $lines[] = '';
        $lines[] = 'Para comparar: corre el comando antes y después del cambio, con los mismos datos.';
        $lines[] = 'La caché de invitaciones estaba '.(config('optimizations.cache.enabled') ? 'encendida' : 'apagada').'.';
        $lines[] = '';

        file_put_contents($path, implode(PHP_EOL, $lines));
        $this->components->info('Guardado en docs/rendimiento.md');
    }
}
