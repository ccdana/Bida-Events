<?php

namespace Tests\Fakes;

use App\Models\PaymentOrder;
use App\Payments\Checkout;
use App\Payments\Contracts\PaymentGateway;
use App\Payments\Exceptions\InvalidWebhookSignature;
use App\Payments\GatewayEvent;
use Illuminate\Http\Request;

/**
 * Pasarela de mentira para las pruebas: firma sus avisos con HMAC como las de verdad. Se registra
 * con config(['payments.drivers.fake' => FakePaymentGateway::class]).
 */
class FakePaymentGateway implements PaymentGateway
{
    public const SECRET = 'secreto-de-prueba';

    public function isConfigured(): bool
    {
        return true;
    }

    public function supportsPlan(string $plan): bool
    {
        return true;
    }

    public function createCheckout(PaymentOrder $order): Checkout
    {
        return new Checkout(redirectUrl: 'https://pasarela.test/pagar/'.$order->public_id, providerRef: 'chk_'.$order->id);
    }

    public function parseWebhook(Request $request): ?GatewayEvent
    {
        if (! hash_equals(self::sign($request->getContent()), (string) $request->header('X-Signature'))) {
            throw new InvalidWebhookSignature('La firma no coincide.');
        }

        $data = $request->json()->all();

        // Un aviso que a la aplicación no le interesa
        if (($data['type'] ?? null) === 'ping') {
            return null;
        }

        return new GatewayEvent(
            id: (string) $data['id'],
            type: (string) $data['type'],
            orderId: $data['order'] ?? null,
            externalId: $data['charge'] ?? null,
            amount: isset($data['amount']) ? (string) $data['amount'] : null,
            currency: $data['currency'] ?? null,
        );
    }

    public function fetchStatus(PaymentOrder $order): ?GatewayEvent
    {
        return null;
    }

    public static function sign(string $body): string
    {
        return hash_hmac('sha256', $body, self::SECRET);
    }
}
