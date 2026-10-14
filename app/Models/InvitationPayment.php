<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Un cobro de «Hazlo tú» ya aplicado: publicó la invitación con un nivel o la subió de nivel
 * (App\Services\InvitationActivator). Guarda el nivel que tenía antes para deshacerlo si se devuelve.
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $invitation_id
 * @property int|null $payment_order_id
 * @property string $tier
 * @property string|null $from_tier
 * @property string $amount
 * @property string $currency
 * @property string $provider
 * @property string|null $external_id
 * @property Carbon $paid_at
 */
class InvitationPayment extends Model
{
    protected $fillable = [
        'user_id', 'invitation_id', 'payment_order_id', 'tier', 'from_tier', 'amount', 'currency', 'provider', 'external_id', 'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(PaymentOrder::class, 'payment_order_id');
    }
}
