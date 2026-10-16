<?php

namespace App\Jobs;

use App\Models\WebhookEvent;
use App\Payments\WebhookProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

/**
 * Aplica en la cola un aviso de una pasarela, para responderle enseguida. Si falla (la base no
 * responde…), se reintenta; el aviso queda guardado con su error y «bida:reprocesar-avisos» lo
 * vuelve a encolar.
 */
class ProcessWebhookEvent implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public array $backoff = [10, 60, 300, 900];

    public function __construct(public int $webhookEventId) {}

    public function handle(WebhookProcessor $processor): void
    {
        $webhook = WebhookEvent::find($this->webhookEventId);

        if (! $webhook) {
            return;
        }

        try {
            $processor->process($webhook);
        } catch (Throwable $e) {
            $webhook->markFailed($e);

            throw $e;
        }
    }
}
