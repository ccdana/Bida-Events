<?php

namespace App\Payments;

/**
 * Un aviso de una pasarela traducido a lo que la aplicación entiende. Cada pasarela arma el suyo
 * desde su propio formato (App\Payments\Contracts\PaymentGateway::parseWebhook) y desde aquí todo es
 * igual, venga de la tarjeta o del QR.
 */
final class GatewayEvent
{
    /** La pasarela cobró una orden nuestra. */
    public const ORDER_PAID = 'order.paid';

    /** El cobro de la orden fue rechazado. */
    public const ORDER_FAILED = 'order.failed';

    /** La orden venció sin pagarse (un QR que nadie escaneó). */
    public const ORDER_EXPIRED = 'order.expired';

    /**
     * Cambió una suscripción que se renueva sola: se creó, se canceló, quedó con el pago atrasado,
     * venció, cambió de plan… $externalId es el id de la suscripción en la pasarela y $extra trae
     * status (active|past_due|cancelled|expired), cancelled, plan y customer_id.
     */
    public const SUBSCRIPTION_UPDATED = 'subscription.updated';

    /** La pasarela cobró la renovación mensual. $extra: subscription_id, plan, charged_amount, charged_currency. */
    public const SUBSCRIPTION_RENEWED = 'subscription.renewed';

    /** Falló el cobro de una renovación (la pasarela lo reintenta). $extra: subscription_id. */
    public const SUBSCRIPTION_PAYMENT_FAILED = 'subscription.payment_failed';

    /** Se devolvió un cobro. $externalId es el cobro devuelto; $extra: full (si se devolvió completo). */
    public const PAYMENT_REFUNDED = 'payment.refunded';

    /**
     * @param  string  $id  Id del aviso en la pasarela: con él se descartan los repetidos
     * @param  string  $type  Uno de los tipos de arriba o el nombre original de la pasarela (se guarda sin acción)
     * @param  string|null  $orderId  public_id de nuestra orden (PaymentOrder)
     * @param  string|null  $externalId  Id del cobro en la pasarela
     * @param  string|null  $amount  Monto cobrado, para comprobar que coincide con la orden
     * @param  array<string, mixed>  $extra  Lo demás que haga falta guardar
     */
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly ?string $orderId = null,
        public readonly ?string $externalId = null,
        public readonly ?string $amount = null,
        public readonly ?string $currency = null,
        public readonly array $extra = [],
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'order_id' => $this->orderId,
            'external_id' => $this->externalId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'extra' => $this->extra,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) $data['id'],
            type: (string) $data['type'],
            orderId: $data['order_id'] ?? null,
            externalId: $data['external_id'] ?? null,
            amount: isset($data['amount']) ? (string) $data['amount'] : null,
            currency: $data['currency'] ?? null,
            extra: $data['extra'] ?? [],
        );
    }
}
