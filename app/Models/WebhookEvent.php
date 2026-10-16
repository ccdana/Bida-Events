<?php

namespace App\Models;

use App\Payments\GatewayEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Throwable;

/**
 * Un aviso recibido de una pasarela. Se guarda una sola vez por (pasarela, id del aviso): las
 * pasarelas reintentan sus avisos, y uno repetido no debe volver a cobrar ni sumar meses. Se procesa
 * después en la cola (App\Jobs\ProcessWebhookEvent); si falla, queda con su error para revisarlo.
 *
 * @property int $id
 * @property string $provider
 * @property string $event_id
 * @property string $type
 * @property array $data
 * @property string|null $payload
 * @property int $attempts
 * @property Carbon|null $processed_at
 * @property string|null $error
 */
class WebhookEvent extends Model
{
    protected $fillable = ['provider', 'event_id', 'type', 'data', 'payload'];

    protected $casts = [
        'data' => 'array',
        'attempts' => 'integer',
        'processed_at' => 'datetime',
    ];

    /** Guarda el aviso; null si ya se había recibido (la pasarela lo reintentó). */
    public static function record(string $provider, GatewayEvent $event, ?string $payload): ?self
    {
        $inserted = self::query()->insertOrIgnore([
            'provider' => $provider,
            'event_id' => $event->id,
            'type' => $event->type,
            'data' => json_encode($event->toArray()),
            'payload' => $payload,
            'attempts' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($inserted === 0) {
            return null;
        }

        return self::where('provider', $provider)->where('event_id', $event->id)->firstOrFail();
    }

    public function gatewayEvent(): GatewayEvent
    {
        return GatewayEvent::fromArray($this->data);
    }

    public function isProcessed(): bool
    {
        return $this->processed_at !== null;
    }

    /** Procesado. Con nota cuando no se pudo aplicar pero reintentar no lo arreglaría (orden desconocida…). */
    public function markProcessed(?string $note = null): void
    {
        $this->forceFill(['processed_at' => now(), 'error' => $note])->save();
    }

    public function markFailed(Throwable|string $error): void
    {
        $message = $error instanceof Throwable ? $error::class.': '.$error->getMessage() : $error;

        $this->forceFill(['error' => Str::limit($message, 2000)])->save();
    }
}
