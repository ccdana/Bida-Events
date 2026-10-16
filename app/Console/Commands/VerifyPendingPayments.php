<?php

namespace App\Console\Commands;

use App\Jobs\ProcessWebhookEvent;
use App\Models\PaymentOrder;
use App\Models\WebhookEvent;
use App\Payments\PaymentGateways;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

/**
 * El respaldo de los avisos de las pasarelas (cada 5 minutos, routes/console.php):
 * 1. Pregunta por los cobros pendientes de los últimos días. Si la pasarela confirma uno (un QR
 *    pagado cuyo aviso no llegó), se aplica igual que su aviso: una sola vez.
 * 2. Da por vencidos los que pasaron su fecha sin pagarse. Si después se pagan, se activan igual.
 * Las pasarelas que no permiten consultar (Lemon Squeezy) solo pasan por el paso 2.
 */
class VerifyPendingPayments extends Command
{
    protected $signature = 'bida:verificar-pagos';

    protected $description = 'Consulta a las pasarelas los cobros pendientes y vence los que no se pagaron';

    public function handle(PaymentGateways $gateways): int
    {
        $confirmed = 0;

        PaymentOrder::pending()
            ->where('created_at', '>=', now()->subDays(3))
            ->orderBy('id')
            ->get()
            ->each(function (PaymentOrder $order) use ($gateways, &$confirmed) {
                if (! $gateways->has($order->provider) || ! ($gateway = $gateways->get($order->provider))->isConfigured()) {
                    return;
                }

                $event = rescue(fn () => $gateway->fetchStatus($order));
                $webhook = $event ? WebhookEvent::record($order->provider, $event, null) : null;

                if ($webhook) {
                    rescue(fn () => Bus::dispatch(new ProcessWebhookEvent($webhook->id)));
                    $confirmed++;
                }
            });

        $expired = PaymentOrder::pending()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now()->subHour())
            ->get()
            ->filter(fn (PaymentOrder $order) => $order->close(PaymentOrder::EXPIRED, 'Venció sin pagarse'))
            ->count();

        $this->info("Cobros confirmados: {$confirmed}. Vencidos: {$expired}.");

        return self::SUCCESS;
    }
}
