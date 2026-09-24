<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
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
            'subscription_renews_at' => 'date',
        ];
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

    /** Pagos de suscripción registrados por el administrador (solo revendedores). */
    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
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
     * La suscripción vale mientras esté activa y su fecha de renovación todavía no haya llegado.
     * El día exacto de la renovación ya cuenta como vencida: ese día hay que pagar.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscription_status === 'active'
            && $this->subscription_renews_at !== null
            && $this->subscription_renews_at->isFuture();
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
