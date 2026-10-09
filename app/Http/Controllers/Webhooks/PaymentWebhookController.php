<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWebhookEvent;
use App\Models\WebhookEvent;
use App\Payments\Exceptions\InvalidWebhookSignature;
use App\Payments\PaymentGateways;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

/**
 * Donde las pasarelas avisan lo que pasó con un cobro (/webhooks/lemonsqueezy, /webhooks/cucu…).
 * Se comprueba la firma, se guarda el aviso una sola vez y se responde enseguida; aplicarlo
 * (activar la suscripción) se hace en la cola. Sin sesión ni CSRF: lo llama un servidor, no un navegador.
 */
class PaymentWebhookController extends Controller
{
    public function __invoke(Request $request, string $provider, PaymentGateways $gateways): Response
    {
        abort_unless($gateways->has($provider), 404);

        try {
            $event = $gateways->get($provider)->parseWebhook($request);
        } catch (InvalidWebhookSignature $e) {
            Log::channel('operations')->warning('Aviso de pago con firma inválida', ['provider' => $provider, 'ip' => $request->ip(), 'reason' => $e->getMessage()]);

            return response('Firma inválida', 401);
        }

        // Un aviso que no nos interesa: se confirma para que la pasarela no lo reintente
        if (! $event) {
            return response('Sin acción', 200);
        }

        // null: ya lo habíamos recibido (la pasarela reintenta); no se vuelve a aplicar
        $webhook = WebhookEvent::record($provider, $event, $request->getContent());

        // Ya está guardado: si aplicarlo falla, queda pendiente con su error (bida:salud lo avisa y
        // bida:reprocesar-avisos lo reintenta). A la pasarela se le confirma igual, porque un
        // reintento suyo llegaría como repetido y no se aplicaría.
        // (Bus::dispatch encola en el momento; ::dispatch() lo haría al final, fuera del rescue)
        if ($webhook) {
            rescue(fn () => Bus::dispatch(new ProcessWebhookEvent($webhook->id)));
        }

        return response('Recibido', 200);
    }
}
