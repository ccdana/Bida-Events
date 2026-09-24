<?php

namespace App\Support;

use App\Models\Guest;
use App\Models\Invitation;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * El pase de entrada de un invitado: lo que lleva su código QR y el código corto que se dicta.
 *
 * El QR lleva un enlace (/entrada/{invitación}/{pase}) y no el código pelado: así lo lee la cámara
 * de cualquier teléfono, sin instalar nada. Si lo abre el personal de la puerta (con el enlace de
 * puerta abierto antes en ese teléfono) ve el control de ingreso; si lo abre cualquier otra persona,
 * ve la invitación del invitado. Ver App\Http\Controllers\Public\DoorController.
 */
final class GuestPass
{
    /** Largo del código corto que se muestra bajo el QR y que se puede escribir a mano en la puerta. */
    public const CODE_LENGTH = 8;

    public static function url(Invitation|string $invitation, Guest|string $guest): string
    {
        $slug = $invitation instanceof Invitation ? $invitation->slug : $invitation;
        $token = $guest instanceof Guest ? $guest->qr_code_token : $guest;

        return route('door.entry', ['slug' => $slug, 'token' => $token]);
    }

    /** El QR del pase en SVG. */
    public static function svg(Invitation|string $invitation, Guest|string $guest, int $size = 200): string
    {
        return (string) QrCode::size($size)->margin(1)->errorCorrection('M')->generate(self::url($invitation, $guest));
    }

    /** Código corto en mayúsculas, el que se lee en voz alta si el QR no se puede escanear. */
    public static function code(Guest|string $guest): string
    {
        $token = $guest instanceof Guest ? $guest->qr_code_token : $guest;

        return strtoupper(substr((string) $token, 0, self::CODE_LENGTH));
    }

    /** Cuántas personas trae el pase: las confirmadas o, si no confirmó, las invitadas. */
    public static function expected(Guest $guest): int
    {
        return max(1, (int) ($guest->status === 'confirmed' && $guest->passes_confirmed > 0 ? $guest->passes_confirmed : $guest->passes_allocated));
    }

    /** Cuántas personas del pase faltan por entrar. */
    public static function remaining(Guest $guest): int
    {
        return max(0, self::expected($guest) - (int) $guest->checked_in_passes);
    }
}
