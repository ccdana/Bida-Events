<?php

namespace App\Support;

use App\Models\Invitation;
use App\Models\User;

/**
 * Qué incluye cada paquete (Básico, Estándar, Premium) de una invitación armada por el equipo.
 *
 * Cada función aparece en un paquete y sigue en los de arriba. Una invitación sin paquete (las que
 * arma un revendedor, las tarjetas y las anteriores a los paquetes) tiene todo: su precio lo define
 * el plan del revendedor o la temporada, no esta lista.
 *
 * - Básico: portada, cuenta regresiva y agendar, ubicación, itinerario y música.
 * - Estándar: galería y video, dress code, padrinos, hashtag, mesa de regalos, enlaces personales y
 *   confirmación por WhatsApp (el invitado le escribe al organizador; no hay pase).
 * - Premium: confirmación con pase QR, control de entrada, panel del cliente, reportes, invitación
 *   para imprimir, encuestas, playlist, fotomural y galería después del evento.
 *
 * La lista que se publica en la portada (config «bida.packages.*.features») describe esto mismo.
 *
 * La confirmación de asistencia son dos módulos y una invitación usa uno solo: por WhatsApp
 * (rsvp_whatsapp, desde Estándar) o con pase QR (rsvp, Premium). Las invitaciones de un revendedor
 * no llevan paquete: lo que puede usar sale de su plan («rsvp» en config bida.reseller_plans):
 * WhatsApp desde Emprendedor; pase QR, control de entrada y reportes en Agencia.
 *
 * «Hazlo tú» (App\Support\DiyInvitations) usa estos mismos niveles y uno más abajo, Gratis: solo la
 * portada, la cuenta regresiva (con agendar) y la ubicación. El equipo no vende Gratis: los paquetes
 * siguen siendo ORDER; TIERS es la escalera completa.
 */
final class Packages
{
    public const FREE = 'gratis';

    public const BASIC = 'basico';

    public const STANDARD = 'estandar';

    public const PREMIUM = 'premium';

    /** Orden de los paquetes que vende el equipo, de menor a mayor. */
    public const ORDER = [self::BASIC, self::STANDARD, self::PREMIUM];

    /** Todos los niveles, de menor a mayor: Gratis solo existe en «Hazlo tú». */
    public const TIERS = [self::FREE, self::BASIC, self::STANDARD, self::PREMIUM];

    /** Lo único que trae una invitación gratuita; el resto de los módulos sin nivel empieza en Básico. */
    public const FREE_MODULES = ['bienvenida', 'cuenta_regresiva', 'agendar', 'ubicacion'];

    /**
     * Desde qué paquete se incluye cada módulo de la invitación. Los que no están aquí (portada,
     * cuenta regresiva, agendar, ubicación, itinerario, música y los de las tarjetas) van en todos.
     */
    public const MODULES = [
        'galeria' => self::STANDARD,
        'video' => self::STANDARD,
        'dress_code' => self::STANDARD,
        'destacados' => self::STANDARD,
        'hashtag' => self::STANDARD,
        'regalos' => self::STANDARD,
        'rsvp_whatsapp' => self::STANDARD,
        'rsvp' => self::PREMIUM,
        'encuestas' => self::PREMIUM,
        'playlist' => self::PREMIUM,
        'fotomural' => self::PREMIUM,
        'post_evento' => self::PREMIUM,
    ];

    /** Desde qué paquete se incluye cada función que no es un módulo. */
    public const FEATURES = [
        'personal_links' => self::STANDARD,
        'rsvp_whatsapp' => self::STANDARD,
        'rsvp_pass' => self::PREMIUM,
        'door' => self::PREMIUM,
        'client_panel' => self::PREMIUM,
        'exports' => self::PREMIUM,
    ];

    /** Confirmación por WhatsApp: el invitado arma el mensaje y se lo envía al organizador. */
    public const RSVP_WHATSAPP = 'whatsapp';

    /** Confirmación guardada en el sistema, con pase QR para la entrada. */
    public const RSVP_PASS = 'pass';

    /** @return array<string, string> clave => nombre, en orden */
    public static function names(): array
    {
        return collect(config('bida.packages', []))->mapWithKeys(fn (array $package) => [$package['key'] => $package['name']])->all();
    }

    /** Nombre de cada nivel, Gratis incluido, en orden: para «Hazlo tú». */
    public static function tierNames(): array
    {
        return [self::FREE => 'Gratis'] + self::names();
    }

    /** Un paquete que vende el equipo (Gratis no lo es). */
    public static function isValid(?string $package): bool
    {
        return in_array($package, self::ORDER, true);
    }

    /** Cualquier nivel, Gratis incluido. */
    public static function isTier(?string $package): bool
    {
        return in_array($package, self::TIERS, true);
    }

    /** ¿El paquete llega al nivel pedido? Sin paquete (o uno desconocido): todo incluido. */
    public static function reaches(?string $package, string $required): bool
    {
        if (! self::isTier($package)) {
            return true;
        }

        return array_search($package, self::TIERS, true) >= array_search($required, self::TIERS, true);
    }

    /** Desde qué nivel se incluye un módulo: Gratis, Básico (todos los que no dicen otra cosa), Estándar o Premium. */
    public static function moduleTier(string $module): string
    {
        return in_array($module, self::FREE_MODULES, true) ? self::FREE : (self::MODULES[$module] ?? self::BASIC);
    }

    /**
     * El nivel que necesita una invitación con estos módulos encendidos: el más alto entre ellos.
     *
     * @param  array<string, bool>  $flags  config.modulos (código => encendido)
     * @param  list<string>|null  $only  los módulos que usa su plantilla; los demás no cuentan
     */
    public static function requiredTier(array $flags, ?array $only = null): string
    {
        $index = 0;

        foreach ($flags as $module => $enabled) {
            if ($enabled && ($only === null || in_array($module, $only, true))) {
                $index = max($index, (int) array_search(self::moduleTier((string) $module), self::TIERS, true));
            }
        }

        return self::TIERS[$index];
    }

    /** El más alto de dos niveles. */
    public static function higherTier(string $a, string $b): string
    {
        return array_search($a, self::TIERS, true) >= array_search($b, self::TIERS, true) ? $a : $b;
    }

    public static function allowsModule(?string $package, string $module): bool
    {
        return self::reaches($package, self::moduleTier($module));
    }

    /**
     * El paquete con el que se decide qué incluye la invitación. La de «Hazlo tú» sin publicar todavía
     * no pagó nada: cuenta como Gratis (en el editor se ve todo, porque la vista previa no es esta invitación).
     */
    private static function effectivePackage(Invitation $invitation): ?string
    {
        return $invitation->is_diy ? ($invitation->package ?: self::FREE) : $invitation->package;
    }

    public static function allows(?string $package, string $feature): bool
    {
        return self::reaches($package, self::FEATURES[$feature] ?? self::BASIC);
    }

    /** El módulo del editor de cada forma de confirmar. */
    public const RSVP_MODULES = [self::RSVP_PASS => 'rsvp', self::RSVP_WHATSAPP => 'rsvp_whatsapp'];

    /**
     * Formas de confirmar que puede usar esta invitación: las de su paquete o, si la armó un
     * revendedor, las de su plan.
     *
     * @return list<string>
     */
    public static function rsvpModesFor(Invitation $invitation): array
    {
        if ($invitation->reseller_id !== null) {
            $plan = self::resellerPlan($invitation);

            return array_values(array_intersect([self::RSVP_PASS, self::RSVP_WHATSAPP], (array) ($plan['rsvp'] ?? [])));
        }

        $package = self::effectivePackage($invitation);

        return array_values(array_filter([
            self::allows($package, 'rsvp_pass') ? self::RSVP_PASS : null,
            self::allows($package, 'rsvp_whatsapp') ? self::RSVP_WHATSAPP : null,
        ]));
    }

    /**
     * ¿Esta invitación puede usar esta función? Con paquete, lo que dice FEATURES. De un revendedor:
     * el pase QR, el control de entrada y los reportes van con la confirmación con pase de su plan;
     * los enlaces personales y el acceso de su cliente, siempre.
     */
    public static function allowsFor(Invitation $invitation, string $feature): bool
    {
        if ($invitation->reseller_id === null) {
            return self::allows(self::effectivePackage($invitation), $feature);
        }

        return match ($feature) {
            'rsvp_pass', 'door', 'exports' => in_array(self::RSVP_PASS, self::rsvpModesFor($invitation), true),
            'rsvp_whatsapp' => in_array(self::RSVP_WHATSAPP, self::rsvpModesFor($invitation), true),
            default => true,
        };
    }

    /** ¿Esta invitación puede encender este módulo? Lo mismo que allowsFor, por módulo. */
    public static function allowsModuleFor(Invitation $invitation, string $module): bool
    {
        $mode = array_search($module, self::RSVP_MODULES, true);

        if ($invitation->reseller_id !== null) {
            return $mode === false || in_array($mode, self::rsvpModesFor($invitation), true);
        }

        return self::allowsModule(self::effectivePackage($invitation), $module);
    }

    /** El plan del revendedor que armó la invitación (config bida.reseller_plans), o []. */
    private static function resellerPlan(Invitation $invitation): array
    {
        $reseller = $invitation->relationLoaded('reseller')
            ? $invitation->reseller
            : User::select('id', 'reseller_plan')->find($invitation->reseller_id);

        return $reseller?->planConfig() ?? [];
    }

    /** Nombre del paquete desde el que se incluye un módulo («Estándar», «Premium»), para el editor. */
    public static function moduleTierName(string $module): ?string
    {
        $tier = self::MODULES[$module] ?? null;

        return $tier ? (self::names()[$tier] ?? ucfirst($tier)) : null;
    }
}
