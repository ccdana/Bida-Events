<?php

namespace App\Console\Commands;

use App\Models\GuestContribution;
use App\Models\Invitation;
use App\Models\InvitationExport;
use Illuminate\Console\Command;

/**
 * Retención: las fotos del fotomural ocupan espacio en Cloudinary mucho después de la fiesta.
 * Este comando borra las de eventos que ya pasaron hace más de N días, con su archivo remoto.
 * Las canciones se conservan porque son solo texto; con --canciones también se borran.
 */
class PurgeOldContributions extends Command
{
    protected $signature = 'invitations:purge-contributions
                            {--days= : Días después del evento (por defecto, config optimizations.retention.photos_days)}
                            {--canciones : Borra también las canciones sugeridas}
                            {--dry-run : Muestra lo que borraría, sin borrar nada}';

    protected $description = 'Borra fotos (y opcionalmente canciones) de eventos que ya pasaron hace tiempo';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('optimizations.retention.photos_days', 180));
        $dryRun = (bool) $this->option('dry-run');
        $types = $this->option('canciones') ? ['live_photo', 'song_request'] : ['live_photo'];
        $limit = now()->subDays($days)->toDateString();

        $invitationIds = Invitation::query()
            ->whereDate('event_date', '<', $limit)
            ->pluck('id');

        if ($invitationIds->isEmpty()) {
            $this->components->info("No hay eventos anteriores a {$limit}.");

            return self::SUCCESS;
        }

        $query = GuestContribution::query()
            ->whereIn('invitation_id', $invitationIds)
            ->whereIn('type', $types);

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->components->info("Nada que borrar en eventos anteriores a {$limit}.");

            return self::SUCCESS;
        }

        $this->components->info(($dryRun ? 'Simulación: ' : '')."{$total} aportes de eventos anteriores a {$limit} ({$days} días).");

        if ($dryRun) {
            return self::SUCCESS;
        }

        $deleted = 0;
        $photos = 0;

        // El archivo en Cloudinary lo borra el modelo al eliminar la fila (GuestContribution::booted)
        $query->chunkById(200, function ($contributions) use (&$deleted, &$photos) {
            foreach ($contributions as $contribution) {
                if ($contribution->type === 'live_photo' && $contribution->file_path) {
                    $photos++;
                }

                $contribution->delete();
                $deleted++;
            }
        });

        $this->components->info("Listo: {$deleted} aportes borrados, {$photos} con su archivo en Cloudinary.");
        $this->purgeExports();

        return self::SUCCESS;
    }

    /** Los Excel y PDF que pidió el cliente se guardan pocos días: después se vuelven a generar. */
    private function purgeExports(): void
    {
        $days = (int) config('optimizations.retention.exports_days', 7);
        $exports = InvitationExport::where('created_at', '<', now()->subDays($days))->get();

        foreach ($exports as $export) {
            $export->deleteFile();
            $export->delete();
        }

        if ($exports->isNotEmpty()) {
            $this->components->info("Se borraron {$exports->count()} archivos exportados de más de {$days} días.");
        }
    }
}
