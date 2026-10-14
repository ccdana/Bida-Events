<?php

namespace App\Support;

use App\EventProfiles\EventProfiles;
use App\Models\Invitation;
use App\Models\User;
use App\Modules\ModuleRegistry;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * «Hazlo tú»: la invitación que arma su propio dueño desde el sitio, sin pagar nada antes.
 *
 * Mientras es borrador no lleva paquete y tiene todo desbloqueado: se prueba completa. Al publicarla,
 * el nivel sale de las partes encendidas (Packages::requiredTier): Gratis, Básico, Estándar o Premium.
 * La gratuita apaga lo que no incluye y lleva la marca de Bida Events a la vista; una por cuenta.
 * Ya publicada, «package» guarda el nivel pagado: lo que pida más no se ve hasta subir de nivel.
 *
 * Todo sale de config/bida.php («diy»).
 */
final class DiyInvitations
{
    /** Puede armar sus propias invitaciones: cualquier cuenta del sitio que no sea del equipo ni revendedor. */
    public static function canBuild(User $user): bool
    {
        return ! $user->isAdmin() && ! $user->isReseller();
    }

    /** @return HasMany<Invitation, User> */
    public static function of(User $user): HasMany
    {
        return $user->invitations()->where('is_diy', true);
    }

    /** Borradores que nunca se publicaron (los que todavía no tienen nivel). */
    public static function draftsCount(User $user): int
    {
        return self::of($user)->whereNull('package')->count();
    }

    public static function draftsLimit(): int
    {
        return max(1, (int) config('bida.diy.drafts_limit', 3));
    }

    public static function canCreateDraft(User $user): bool
    {
        return self::canBuild($user) && self::draftsCount($user) < self::draftsLimit();
    }

    /** ¿Le queda una invitación gratuita? La que ya es gratuita no cuenta contra sí misma. */
    public static function freeAvailable(User $user, ?Invitation $except = null): bool
    {
        $used = self::of($user)
            ->where('package', Packages::FREE)
            ->when($except?->exists, fn ($query) => $query->whereKeyNot($except->id))
            ->count();

        return $used < max(0, (int) config('bida.diy.free_per_account', 1));
    }

    /**
     * Plantillas que se ofrecen, con el mismo formato que InvitationTemplates::all(): las de las
     * familias de config «diy.collections» (las de temporada se venden aparte).
     *
     * @return array<string, array<string, mixed>>
     */
    public static function allowedTemplates(): array
    {
        $collections = (array) config('bida.diy.collections', []);

        return array_filter(
            InvitationTemplates::all(),
            fn (array $template) => in_array($template['collection'] ?? 'clasica', $collections, true),
        );
    }

    /** Módulos que usa la plantilla de la invitación: los demás no cuentan para el nivel. */
    private static function profileModules(Invitation $invitation): array
    {
        return app(EventProfiles::class)->forTemplate($invitation->template)->modules();
    }

    /** El nivel que piden las partes encendidas. */
    public static function requiredTier(Invitation $invitation, array $modules): string
    {
        return Packages::requiredTier((array) ($modules['config']['modulos'] ?? []), self::profileModules($invitation));
    }

    /**
     * El nivel con el que se publica pagando: el que piden sus partes y, si ya no le queda una
     * gratuita (y esta no lo es), por lo menos Básico.
     */
    public static function publishTier(Invitation $invitation, array $modules): string
    {
        $required = self::requiredTier($invitation, $modules);

        if ($required === Packages::FREE && $invitation->package !== Packages::FREE && ! self::freeAvailable($invitation->user, $invitation)) {
            return Packages::BASIC;
        }

        return $required;
    }

    /** Ya tiene pagado lo que piden sus partes (o es gratuita y no pide más). */
    public static function isCovered(Invitation $invitation, array $modules): bool
    {
        return Packages::isTier($invitation->package)
            && Packages::reaches($invitation->package, self::requiredTier($invitation, $modules));
    }

    /**
     * Partes encendidas que piden más que $tier, con su nombre y el nivel desde el que vienen:
     * lo que se apaga al publicar gratis o lo que no se ve hasta subir de nivel.
     *
     * @return list<array{code: string, label: string, tier: string, tierName: string}>
     */
    public static function modulesAbove(Invitation $invitation, array $modules, string $tier): array
    {
        $flags = (array) ($modules['config']['modulos'] ?? []);
        $registry = app(ModuleRegistry::class);
        $names = Packages::tierNames();
        $above = [];

        foreach (self::profileModules($invitation) as $code) {
            if (! ($flags[$code] ?? false) || Packages::reaches($tier, Packages::moduleTier($code)) || ! $registry->has($code)) {
                continue;
            }

            $moduleTier = Packages::moduleTier($code);
            $above[] = [
                'code' => $code,
                'label' => InvitationPage::NAV_LABELS[$code] ?? $registry->get($code)->label(),
                'tier' => $moduleTier,
                'tierName' => $names[$moduleTier] ?? $moduleTier,
            ];
        }

        return $above;
    }

    /** Apaga las partes que piden más que $tier (su contenido se conserva para volver a encenderlas). */
    public static function withoutModulesAbove(Invitation $invitation, array $modules, string $tier): array
    {
        foreach (self::modulesAbove($invitation, $modules, $tier) as $module) {
            $modules['config']['modulos'][$module['code']] = false;
        }

        return $modules;
    }

    /** Hasta cuándo queda en línea: unos días después del evento, menos si es gratuita. */
    public static function expiresAt(CarbonInterface $eventDate, ?string $package): Carbon
    {
        $days = (array) config('bida.diy.days_after_event', []);
        $after = $package === Packages::FREE ? (int) ($days['gratis'] ?? 7) : (int) ($days['paid'] ?? 30);

        return Carbon::instance($eventDate)->timezone(config('app.timezone'))->startOfDay()->addDays(max(0, $after));
    }
}
