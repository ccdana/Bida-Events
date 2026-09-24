<?php

namespace App\Support;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Reglas de la suscripción de un revendedor: cuántas invitaciones le quedan este mes, qué
 * plantillas puede usar y hasta cuándo se extiende cuando paga.
 *
 * Todo sale de config/bida.php («reseller_plans»): cambiar un precio, un cupo o el catálogo de un
 * plan no requiere tocar código.
 */
final class ResellerSubscription
{
    /** Días antes del vencimiento en que se avisa (al revendedor y al administrador). */
    public const WARNING_DAYS = 5;

    /** Invitaciones que el revendedor creó desde el primer día del mes, en la hora de Bolivia. */
    public static function quotaUsed(User $user, ?CarbonInterface $now = null): int
    {
        $monthStart = Carbon::instance($now ?? now())->timezone(config('app.timezone'))->startOfMonth();

        return $user->resellerInvitations()->where('created_at', '>=', $monthStart)->count();
    }

    /** Tope mensual del plan; null = sin tope. Sin plan (o con uno que ya no existe) no hay cupo. */
    public static function quotaLimit(User $user): ?int
    {
        $plan = $user->planConfig();

        if ($plan === null) {
            return 0;
        }

        return isset($plan['quota_per_month']) ? (int) $plan['quota_per_month'] : null;
    }

    /** Cuántas le quedan este mes; null = sin tope. */
    public static function quotaLeft(User $user, ?CarbonInterface $now = null): ?int
    {
        $limit = self::quotaLimit($user);

        return $limit === null ? null : max(0, $limit - self::quotaUsed($user, $now));
    }

    public static function hasQuotaLeft(User $user, ?CarbonInterface $now = null): bool
    {
        $left = self::quotaLeft($user, $now);

        return $left === null || $left > 0;
    }

    /**
     * Hasta cuándo queda pagada la suscripción después de un pago. Si todavía no venció, el ciclo
     * se suma al vencimiento vigente (pagar antes no hace perder días); si ya venció, cuenta desde hoy.
     */
    public static function renewalDate(User $user, string $plan, ?CarbonInterface $today = null): Carbon
    {
        $today = Carbon::instance($today ?? now())->timezone(config('app.timezone'))->startOfDay();
        $current = $user->subscription_renews_at?->copy()->startOfDay();
        $from = $current && $current->greaterThan($today) ? $current : $today;
        $months = max(1, (int) config("bida.reseller_plans.{$plan}.cycle_months", 1));

        return $from->copy()->addMonthsNoOverflow($months);
    }

    /** Días que faltan para la renovación (negativos si ya pasó); null si nunca pagó. */
    public static function daysLeft(User $user, ?CarbonInterface $today = null): ?int
    {
        if ($user->subscription_renews_at === null) {
            return null;
        }

        $today = Carbon::instance($today ?? now())->timezone(config('app.timezone'))->startOfDay();

        return (int) $today->diffInDays($user->subscription_renews_at->copy()->startOfDay(), false);
    }

    /** Vence dentro de los próximos días de aviso, o ya venció. */
    public static function expiresSoon(User $user, ?CarbonInterface $today = null): bool
    {
        $days = self::daysLeft($user, $today);

        return $days === null || $days <= self::WARNING_DAYS;
    }

    /** Revendedores que vencen en los próximos días de aviso o ya vencieron: el aviso del panel admin. */
    public static function dueSoonCount(?CarbonInterface $today = null): int
    {
        $limit = Carbon::instance($today ?? now())->timezone(config('app.timezone'))->startOfDay()->addDays(self::WARNING_DAYS);

        return User::query()
            ->where('is_reseller', true)
            ->whereNotNull('subscription_renews_at')
            ->whereDate('subscription_renews_at', '<=', $limit)
            ->count();
    }

    /**
     * Plantillas que el plan deja usar, con el mismo formato que InvitationTemplates::all(): las de
     * sus familias («collections», null = todas) y, si el plan trae «templates», solo esas.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function allowedTemplates(User $user): array
    {
        $plan = $user->planConfig() ?? [];
        $collections = $plan['collections'] ?? null;
        $allowed = $plan['templates'] ?? null;

        return array_filter(
            InvitationTemplates::all(),
            fn (array $template, string $key) => (! is_array($collections) || in_array($template['collection'] ?? 'clasica', $collections, true))
                && (! is_array($allowed) || in_array($key, $allowed, true)),
            ARRAY_FILTER_USE_BOTH
        );
    }

    /** Si el plan le deja crear un acceso para el cliente de cada evento. */
    /**
     * Accesos de cliente creados este mes que siguen existiendo: uno que se borró por error no
     * cuenta, así el revendedor puede crear el correcto.
     */
    public static function clientsUsed(User $user, ?CarbonInterface $now = null): int
    {
        $monthStart = Carbon::instance($now ?? now())->timezone(config('app.timezone'))->startOfMonth();

        return $user->resellerClients()->where('created_at', '>=', $monthStart)->count();
    }

    /** Accesos que todavía puede crear este mes; null = sin tope. */
    public static function clientsLeft(User $user, ?CarbonInterface $now = null): ?int
    {
        $limit = self::quotaLimit($user);

        return $limit === null ? null : max(0, $limit - self::clientsUsed($user, $now));
    }

    /**
     * Todos los planes crean accesos para sus clientes, pero no más por mes que las invitaciones
     * que pueden crear (el mismo cupo del plan).
     */
    public static function canCreateClients(User $user, ?CarbonInterface $now = null): bool
    {
        if ($user->planConfig() === null) {
            return false;
        }

        $left = self::clientsLeft($user, $now);

        return $left === null || $left > 0;
    }
}
