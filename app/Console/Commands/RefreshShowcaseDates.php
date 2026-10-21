<?php

namespace App\Console\Commands;

use App\Models\Invitation;
use Database\Seeders\ShowcaseInvitationsSeeder;
use Illuminate\Console\Command;

/**
 * Las muestras de la portada y de las páginas por evento se enseñan como una invitación a la que le faltan
 * unas semanas. Este comando corre a diario y, solo en las muestras cuya fecha ya quedó cerca o pasó, vuelve
 * a sembrarlas con la fecha al día (App\Support\ShowcaseDates). Si ninguna cambió, no toca nada.
 */
class RefreshShowcaseDates extends Command
{
    protected $signature = 'bida:renovar-muestras
                            {--forzar : Vuelve a sembrar todas las muestras, aunque su fecha esté al día}
                            {--dry-run : Muestra cuáles cambiaría, sin cambiar nada}';

    protected $description = 'Corre las fechas de las invitaciones de muestra para que nunca se vean como un evento terminado';

    public function handle(): int
    {
        $seeder = new ShowcaseInvitationsSeeder;
        $current = Invitation::query()
            ->whereIn('slug', ShowcaseInvitationsSeeder::slugs())
            ->get(['slug', 'event_date', 'expires_at'])
            ->keyBy('slug');

        $changes = [];
        $missing = [];

        foreach (ShowcaseInvitationsSeeder::slugs() as $slug) {
            $target = ShowcaseInvitationsSeeder::data($slug)['invitation'];
            $saved = $current->get($slug);

            if ($saved === null) {
                $missing[] = $slug;

                continue;
            }

            $upToDate = $saved->event_date?->format('Y-m-d H:i:s') === $target['event_date']
                && $saved->expires_at?->toDateString() === $target['expires_at'];

            if ($upToDate && ! $this->option('forzar')) {
                continue;
            }

            $changes[] = [$slug, $saved->event_date?->toDateString(), substr($target['event_date'], 0, 10)];

            if (! $this->option('dry-run')) {
                $seeder->seedSlug($slug);
            }
        }

        if ($missing !== []) {
            $this->components->warn('Sin sembrar (se omiten): '.implode(', ', $missing).'. Corre db:seed --class=ShowcaseInvitationsSeeder.');
        }

        if ($changes === []) {
            $this->components->info('Las fechas de las muestras están al día.');

            return self::SUCCESS;
        }

        $this->table(['Muestra', 'Fecha anterior', 'Fecha nueva'], $changes);
        $this->components->info(count($changes).($this->option('dry-run') ? ' muestras cambiarían.' : ' muestras renovadas.'));

        return self::SUCCESS;
    }
}
