<?php

namespace App\Support;

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
 */
final class Packages
{
    public const BASIC = 'basico';

    public const STANDARD = 'estandar';

    public const PREMIUM = 'premium';

    /** Orden de los paquetes, de menor a mayor. */
    public const ORDER = [self::BASIC, self::STANDARD, self::PREMIUM];

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
        'rsvp' => self::STANDARD,
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

    public static function isValid(?string $package): bool
    {
        return in_array($package, self::ORDER, true);
    }

    /** ¿El paquete llega al nivel pedido? Sin paquete (o uno desconocido): todo incluido. */
    public static function reaches(?string $package, string $required): bool
    {
        if (! self::isValid($package)) {
            return true;
        }

        return array_search($package, self::ORDER, true) >= array_search($required, self::ORDER, true);
    }

    public static function allowsModule(?string $package, string $module): bool
    {
        return self::reaches($package, self::MODULES[$module] ?? self::BASIC);
    }

    public static function allows(?string $package, string $feature): bool
    {
        return self::reaches($package, self::FEATURES[$feature] ?? self::BASIC);
    }

    /** Cómo confirma el invitado: con pase (Premium o sin paquete), por WhatsApp (Estándar) o no confirma (Básico). */
    public static function rsvpMode(?string $package): ?string
    {
        return match (true) {
            self::allows($package, 'rsvp_pass') => self::RSVP_PASS,
            self::allows($package, 'rsvp_whatsapp') => self::RSVP_WHATSAPP,
            default => null,
        };
    }

    /** Nombre del paquete desde el que se incluye un módulo («Estándar», «Premium»), para el editor. */
    public static function moduleTierName(string $module): ?string
    {
        $tier = self::MODULES[$module] ?? null;

        return $tier ? (self::names()[$tier] ?? ucfirst($tier)) : null;
    }
}
