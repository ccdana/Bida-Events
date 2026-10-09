<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Un pago de suscripción de un revendedor: registrado a mano por el administrador («manual») o
 * cobrado en línea por una pasarela (con su orden y el id del cobro en la pasarela). Cada pago
 * extiende la suscripción hasta «renews_until» (App\Services\SubscriptionActivator).
 *
 * @property int $id
 * @property int $user_id
 * @property string $plan
 * @property string $amount
 * @property string $currency
 * @property string $provider
 * @property int|null $payment_order_id
 * @property string|null $external_id
 * @property Carbon $paid_at
 * @property Carbon $renews_until
 * @property int|null $registered_by
 * @property string|null $note
 */
class SubscriptionPayment extends Model
{
    public const MANUAL = 'manual';

    protected $fillable = [
        'user_id', 'plan', 'amount', 'currency', 'provider', 'payment_order_id', 'external_id',
        'paid_at', 'renews_until', 'registered_by', 'note', 'request_token',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'date',
        'renews_until' => 'date',
    ];

    /** El revendedor que pagó. */
    public function reseller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** El administrador que registró el pago (solo los registrados a mano). */
    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    /** La compra en línea que lo originó (null en los registrados a mano). */
    public function order(): BelongsTo
    {
        return $this->belongsTo(PaymentOrder::class, 'payment_order_id');
    }

    public function isManual(): bool
    {
        return ($this->provider ?? self::MANUAL) === self::MANUAL;
    }

    /** «registrado a mano», «tarjeta», «QR». */
    public function providerLabel(): string
    {
        $provider = $this->provider ?? self::MANUAL;

        return (string) config("payments.labels.{$provider}", $provider);
    }
}
