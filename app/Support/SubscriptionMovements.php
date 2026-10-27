<?php

namespace App\Support;

use App\Models\User;
use App\Payments\PaymentGateways;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Los movimientos de la suscripción con tarjeta de un cliente, pedidos a la pasarela y guardados un rato: «Mi cuenta» no
 * debe llamar a la pasarela en cada visita ni depender de que responda. Si no hay suscripción con tarjeta, no responde o
 * falla, devuelve una lista vacía (y esa falla se recuerda un minuto para no insistir); la página muestra entonces los
 * pagos que tenemos anotados.
 */
final class SubscriptionMovements
{
    public const KEEP_SECONDS = 300;

    public const KEEP_EMPTY_SECONDS = 60;

    /** @return list<array<string, mixed>> */
    public static function for(User $user, PaymentGateways $gateways): array
    {
        $manager = $user->hasRecurringSubscription() ? $gateways->subscriptionManager($user->payment_provider) : null;

        if (! $manager) {
            return [];
        }

        $key = "subscription-movements:{$user->id}:{$user->provider_subscription_id}";
        $kept = Cache::get($key);

        if (is_array($kept)) {
            return $kept;
        }

        try {
            $movements = $manager->invoices($user);
        } catch (Throwable $exception) {
            report($exception);
            $movements = [];
        }

        Cache::put($key, $movements, $movements === [] ? self::KEEP_EMPTY_SECONDS : self::KEEP_SECONDS);

        return $movements;
    }
}