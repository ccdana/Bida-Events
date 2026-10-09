<?php

namespace App\Payments;

use App\Payments\Contracts\ManagesSubscriptions;
use App\Payments\Contracts\PaymentGateway;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

/**
 * Las pasarelas configuradas (config/payments.php): por su clave («lemonsqueezy», «cucu») o por el
 * método de pago que atienden («card», «qr»).
 */
class PaymentGateways
{
    public const METHODS = ['card', 'qr'];

    public function __construct(private readonly Container $container) {}

    /** Hay una clase para esa pasarela (aunque todavía no tenga credenciales). */
    public function has(string $provider): bool
    {
        $class = config("payments.drivers.{$provider}");

        return is_string($class) && is_subclass_of($class, PaymentGateway::class);
    }

    public function get(string $provider): PaymentGateway
    {
        if (! $this->has($provider)) {
            throw new InvalidArgumentException("No hay una pasarela «{$provider}» configurada.");
        }

        return $this->container->make(config("payments.drivers.{$provider}"));
    }

    /**
     * La clave de la pasarela que atiende hoy un método («card», «qr»): el cobro en línea encendido,
     * la pasarela con credenciales y, si se indica, capaz de cobrar ese plan. null si no hay ninguna.
     */
    public function providerFor(string $method, ?string $plan = null): ?string
    {
        $provider = config("payments.methods.{$method}");

        if (! config('payments.enabled') || ! is_string($provider) || ! $this->has($provider)) {
            return null;
        }

        $gateway = $this->get($provider);

        return $gateway->isConfigured() && ($plan === null || $gateway->supportsPlan($plan)) ? $provider : null;
    }

    /** Los métodos que se pueden ofrecer hoy, con su pasarela: ['card' => 'lemonsqueezy']. */
    public function availableMethods(?string $plan = null): array
    {
        return collect(self::METHODS)
            ->mapWithKeys(fn (string $method) => [$method => $this->providerFor($method, $plan)])
            ->filter()
            ->all();
    }

    /** La pasarela que renueva sola la suscripción de esta persona, si tiene una. */
    public function subscriptionManager(?string $provider): ?ManagesSubscriptions
    {
        if (! $provider || ! $this->has($provider)) {
            return null;
        }

        $gateway = $this->get($provider);

        return $gateway instanceof ManagesSubscriptions && $gateway->isConfigured() ? $gateway : null;
    }
}
