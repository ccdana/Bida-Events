<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Un pago de suscripción que el administrador registró a mano para un revendedor.
 *
 * @property int $id
 * @property int $user_id
 * @property string $plan
 * @property string $amount
 * @property Carbon $paid_at
 * @property Carbon $renews_until
 * @property int|null $registered_by
 * @property string|null $note
 */
class SubscriptionPayment extends Model
{
    protected $fillable = ['user_id', 'plan', 'amount', 'paid_at', 'renews_until', 'registered_by', 'note', 'request_token'];

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

    /** El administrador que registró el pago. */
    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
}
