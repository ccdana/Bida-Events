<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

/**
 * Las cuentas que arma el equipo entran con su usuario y pueden no tener correo. Las que se abren
 * desde el sitio entran con el correo (verificado) o con Google. Solo la compra exige el correo
 * verificado (middleware «verified»); el resto del panel no lo pide.
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'google_id',
        'avatar_url',
        'is_admin',
        'is_reseller',
        'reseller_plan',
        'subscription_status',
        'subscription_renews_at',
        'business_name',
        'logo_path',
        'brand_primary_color',
        'created_by_reseller_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            // Copia legible de la contraseña generada, cifrada con APP_KEY; solo la ve el administrador
            'is_admin' => 'boolean',
            'is_reseller' => 'boolean',
            // Cada cuántos meses se renueva sola con tarjeta (1, 3 o 12)
            'subscription_cycle_months' => 'integer',
            'subscription_renews_at' => 'date',
            'cancel_at_period_end' => 'boolean',
        ];
    }

    /** El correo se guarda en minúsculas y sin espacios: así se busca al iniciar sesión. */
    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => filled($value) ? Str::lower(trim($value)) : null,
        );
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    /** Invitaciones que arma este revendedor para sus propios clientes. */
    public function resellerInvitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'reseller_id');
    }

    /** Clientes que creó este revendedor, uno por evento. */
    public function resellerClients(): HasMany
    {
        return $this->hasMany(User::class, 'created_by_reseller_id');
    }

    /** El revendedor que creó este cliente (null: lo creó el equipo). */
    public function createdByReseller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_reseller_id');
    }

    /** Pagos de suscripción: los registrados a mano y los cobrados en línea. */
    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    /** Compras en línea, pagadas o no. */
    public function paymentOrders(): HasMany
    {
        return $this->hasMany(PaymentOrder::class);
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /** Revendedor: arma sus propias invitaciones dentro del cupo de su plan. */
    public function isReseller(): bool
    {
        return (bool) $this->is_reseller;
    }

    /**
     * La suscripción vale mientras esté al día (o con el pago atrasado, que la pasarela reintenta)
     * y no hayan pasado los días de gracia desde el vencimiento (config «payments.grace_days»). Con
     * 0 días, el día exacto de la renovación ya cuenta como vencida. Cancelada o terminada, no vale.
     */
    public function hasActiveSubscription(): bool
    {
        return in_array($this->subscription_status, ['active', 'past_due'], true)
            && $this->subscription_renews_at !== null
            && $this->subscription_renews_at->copy()->startOfDay()->addDays(max(0, (int) config('payments.grace_days', 0)))->isFuture();
    }

    /**
     * Tiene una suscripción en una pasarela que cobra sola cada mes (tarjeta) y que sigue viva: al
     * día o con un cobro atrasado. Para cambiar de plan o de tarjeta usa el portal de la pasarela.
     */
    public function hasRecurringSubscription(): bool
    {
        return $this->payment_provider !== null
            && $this->provider_subscription_id !== null
            && in_array($this->subscription_status, ['active', 'past_due'], true);
    }

    /** Se va a renovar sola en la próxima fecha (no canceló la renovación). */
    public function renewsAutomatically(): bool
    {
        return $this->hasRecurringSubscription() && ! $this->cancel_at_period_end;
    }

    /** Configuración del plan del revendedor (config/bida.php, «reseller_plans»); null si no tiene. */
    public function planConfig(): ?array
    {
        if (! $this->reseller_plan) {
            return null;
        }

        $plan = config("bida.reseller_plans.{$this->reseller_plan}");

        return is_array($plan) ? $plan : null;
    }
}
