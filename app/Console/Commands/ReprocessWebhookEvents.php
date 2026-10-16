<?php

namespace App\Console\Commands;

use App\Jobs\ProcessWebhookEvent;
use App\Models\WebhookEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

/**
 * Vuelve a encolar los avisos de las pasarelas que no se aplicaron (la cola estaba caída, la base no
 * respondía…). Aplicarlos dos veces no cobra ni suma meses de más: cada pago se registra una sola vez.
 * Con --id se reprocesa uno ya procesado (por ejemplo, después de crear a mano la orden que faltaba).
 */
class ReprocessWebhookEvents extends Command
{
    protected $signature = 'bida:reprocesar-avisos
                            {--id=* : Ids de webhook_events a reprocesar aunque ya estén procesados}';

    protected $description = 'Vuelve a aplicar los avisos de pago que quedaron sin procesar';

    public function handle(): int
    {
        $ids = array_filter(array_map('intval', (array) $this->option('id')));

        $events = $ids !== []
            ? WebhookEvent::whereIn('id', $ids)->get()
            : WebhookEvent::whereNull('processed_at')->orderBy('id')->get();

        if ($events->isEmpty()) {
            $this->info('No hay avisos para reprocesar.');

            return self::SUCCESS;
        }

        // Uno que vuelva a fallar queda con su error y no frena a los demás
        foreach ($events as $event) {
            $event->forceFill(['processed_at' => null, 'error' => null])->save();
            rescue(fn () => Bus::dispatch(new ProcessWebhookEvent($event->id)));
        }

        $this->info("{$events->count()} aviso(s) en la cola.");

        return self::SUCCESS;
    }
}
