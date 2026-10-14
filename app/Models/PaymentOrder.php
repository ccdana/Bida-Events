<?php

namespace App\Models;

use App\Support\Packages;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Un intento de compra en línea. Se abre antes de ir a la pasarela (con el precio de ese momento) y
 * queda «paid» cuando la pasarela avisa que se cobró; recién ahí se registra el pago y se extiende la
 * suscripción (App\Services\SubscriptionActivator) o se publica la invitación de «Hazlo tú» con su
 * nivel (App\Services\InvitationActivator). Las que nunca se pagan quedan «expired».
 *
 * De una invitación («purpose» invitation), «plan» es el nivel que se compra y «meta.from_tier» el
 * que tenía (subir de nivel cobra la diferencia).
 *
 * @property int $id
 * @property string $public_id
 * @property string $reference_code
 * @property int|null $user_id
 * @property int|null $invitation_id
 * @property string $purpose
 * @property string|null $plan
 * @property int $months
 * @property string $amount
 * @property string $currency
 * @property string $provider
 * @property string|null $provider_ref
 * @property string $status
 * @property Carbon|null $expires_at
 * @property Carbon|null $paid_at
 * @property array|null $meta
 */
class PaymentOrder extends Model
{
    public const PENDING = 'pending';

    public const PAID = 'paid';

    public const FAILED = 'failed';

    public const EXPIRED = 'expired';

    public const REFUNDED = 'refunded';

    public const PURPOSE_SUBSCRIPTION = 'subscription';

    /** Publicar o subir de nivel una invitación de «Hazlo tú» (pago único). */
    public const PURPOSE_INVITATION = 'invitation';

    /** Letras y números que no se confunden al leerlos o dictarlos (sin 0/O, 1/I/L). */
    private const CODE_ALPHABET = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    protected $fillable = [
        'user_id', 'invitation_id', 'purpose', 'plan', 'months', 'amount', 'currency', 'provider', 'provider_ref', 'expires_at', 'meta',
    ];

    protected $casts = [
        'months' => 'integer',
        'amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'meta' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (PaymentOrder $order) {
            $order->public_id ??= (string) Str::uuid();
            $order->reference_code ??= self::newReferenceCode();
            $order->status ??= self::PENDING;
        });
    }

    /** Abre la orden de un plan de revendedor, con el monto y la pasarela ya decididos. */
    public static function forSubscription(User $user, string $plan, int $months, string $amount, string $currency, string $provider): self
    {
        return self::create([
            'user_id' => $user->id,
            'purpose' => self::PURPOSE_SUBSCRIPTION,
            'plan' => $plan,
            'months' => max(1, $months),
            'amount' => $amount,
            'currency' => $currency,
            'provider' => $provider,
        ]);
    }

    /**
     * Abre la orden de una invitación de «Hazlo tú»: el nivel que se compra, el que tenía (para cobrar
     * la diferencia y deshacerlo si se devuelve) y el monto ya calculado (App\Payments\InvitationPrice).
     */
    public static function forInvitation(Invitation $invitation, string $tier, string $amount, string $currency, string $provider): self
    {
        return self::create([
            'user_id' => $invitation->user_id,
            'invitation_id' => $invitation->id,
            'purpose' => self::PURPOSE_INVITATION,
            'plan' => $tier,
            'months' => 1,
            'amount' => $amount,
            'currency' => $currency,
            'provider' => $provider,
            'meta' => ['from_tier' => $invitation->package],
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** La invitación que se paga (órdenes de «Hazlo tú»). */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function isForInvitation(): bool
    {
        return $this->purpose === self::PURPOSE_INVITATION;
    }

    /** Qué se compra, como lo lee el comprador en la pasarela y en su banco. */
    public function concept(): string
    {
        if ($this->isForInvitation()) {
            $tier = Packages::tierNames()[$this->plan] ?? $this->plan;
            $verb = Packages::isTier($this->meta['from_tier'] ?? null) && ($this->meta['from_tier'] ?? null) !== Packages::FREE ? 'Subir a' : 'Invitación';

            return "{$verb} {$tier} · «".Str::limit((string) $this->invitation?->title, 60).'»';
        }

        $planName = config("bida.reseller_plans.{$this->plan}.name", $this->plan);

        return "Plan {$planName} de «Únete» · ".($this->months === 1 ? '1 mes' : "{$this->months} meses");
    }

    /** La página a la que vuelve el comprador para esperar el aviso del cobro. */
    public function returnUrl(): string
    {
        return $this->isForInvitation()
            ? route('client.diy.order', $this->public_id)
            : route('client.subscription.order', $this->public_id);
    }

    /** El pago que generó la orden al cobrarse. */
    public function payment(): HasOne
    {
        return $this->hasOne(SubscriptionPayment::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::PENDING);
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }

    public function isPaid(): bool
    {
        return $this->status === self::PAID;
    }

    /** Cierra una orden que no se cobró. Una orden pagada no vuelve atrás por aquí. */
    public function close(string $status, ?string $reason = null): bool
    {
        if (! $this->isPending()) {
            return false;
        }

        $this->status = $status;

        if ($reason !== null) {
            $this->meta = array_merge($this->meta ?? [], ['closed_reason' => $reason]);
        }

        return $this->save();
    }

    private static function newReferenceCode(): string
    {
        do {
            $code = 'BE-'.collect(range(1, 6))
                ->map(fn () => self::CODE_ALPHABET[random_int(0, strlen(self::CODE_ALPHABET) - 1)])
                ->implode('');
        } while (self::where('reference_code', $code)->exists());

        return $code;
    }
}
