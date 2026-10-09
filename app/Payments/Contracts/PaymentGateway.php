<?php

namespace App\Payments\Contracts;

use App\Models\PaymentOrder;
use App\Payments\Checkout;
use App\Payments\Exceptions\InvalidWebhookSignature;
use App\Payments\GatewayEvent;
use Illuminate\Http\Request;

/**
 * Lo que tiene que saber hacer cada pasarela (Lemon Squeezy para la tarjeta, CUCU para el QR…).
 * Se registra en config/payments.php («drivers»). El resto de la aplicación solo habla con esta
 * interfaz: las órdenes, los avisos y la activación de la suscripción son los mismos para todas.
 */
interface PaymentGateway
{
    /** Tiene sus credenciales en .env: sin ellas no se ofrece como forma de pago. */
    public function isConfigured(): bool;

    /** ¿Puede cobrar este plan? (por ejemplo, Lemon Squeezy necesita la variante del plan). */
    public function supportsPlan(string $plan): bool;

    /** Abre el cobro de la orden en la pasarela y dice cómo sigue el comprador. */
    public function createCheckout(PaymentOrder $order): Checkout;

    /**
     * Comprueba que el aviso lo mandó la pasarela (su firma) y lo traduce. null si es un aviso que
     * no interesa (se responde que llegó y no se guarda).
     *
     * @throws InvalidWebhookSignature
     */
    public function parseWebhook(Request $request): ?GatewayEvent;

    /**
     * Pregunta a la pasarela cómo está la orden: el respaldo cuando su aviso no llegó. null si sigue
     * igual o la pasarela no lo permite.
     */
    public function fetchStatus(PaymentOrder $order): ?GatewayEvent;
}
