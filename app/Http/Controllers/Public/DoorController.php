<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\CheckInGuestRequest;
use App\Models\Guest;
use App\Models\Invitation;
use App\Support\GuestPass;
use App\Support\Packages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Control de entrada el día del evento, desde el teléfono del personal de la puerta.
 *
 * 1. El organizador comparte el enlace de puerta (/puerta/{door_token}). Al abrirlo, ese teléfono
 *    guarda una cookie cifrada que lo habilita para esa invitación durante el evento.
 * 2. Cada pase lleva un QR con /entrada/{slug}/{token}. Con la cookie, el enlace muestra si el
 *    invitado puede pasar y cuántas personas le faltan; sin ella, lleva a la invitación del
 *    invitado (así un invitado que escanea su propio QR no ve nada de la puerta).
 * 3. El ingreso se registra con un POST: recargar la página nunca cuenta dos veces.
 *
 * Generar un enlace de puerta nuevo (desde el panel) deja sin acceso a los teléfonos anteriores.
 */
class DoorController extends Controller
{
    /** Cuánto dura habilitado un teléfono de puerta: una noche de fiesta con margen. */
    private const ACCESS_MINUTES = 60 * 16;

    public function open(string $doorToken): View
    {
        $invitation = $this->invitationForDoor($doorToken);

        Cookie::queue(self::cookieName($invitation), $doorToken, self::ACCESS_MINUTES, null, null, null, true, false, 'lax');

        return view('door.scanner', [
            'invitation' => $invitation,
            'doorToken' => $doorToken,
            'stats' => self::stats($invitation),
        ]);
    }

    /** El código corto escrito a mano (o un QR viejo, que traía el código entero). */
    public function lookup(Request $request, string $doorToken): RedirectResponse
    {
        $invitation = $this->invitationForDoor($doorToken);
        $code = strtolower(preg_replace('/[^a-z0-9]/i', '', (string) $request->input('code')) ?? '');

        abort_if(strlen($code) < 4, 422, 'Escribe el código del pase.');

        $guest = Guest::where('invitation_id', $invitation->id)
            ->where(function ($query) use ($code) {
                $query->where('qr_code_token', $code)
                    ->orWhereRaw('LOWER(SUBSTR(qr_code_token, 1, ?)) = ?', [GuestPass::CODE_LENGTH, substr($code, 0, GuestPass::CODE_LENGTH)]);
            })
            ->first();

        if (! $guest) {
            return redirect()->route('door.open', $doorToken)
                ->with('door_error', 'No hay ningún pase con ese código en esta invitación.');
        }

        return redirect()->route('door.entry', ['slug' => $invitation->slug, 'token' => $guest->qr_code_token]);
    }

    public function entry(Request $request, string $slug, string $token): View|RedirectResponse
    {
        [$invitation, $guest] = $this->findPass($slug, $token);

        // Sin el enlace de puerta abierto en este teléfono, es un invitado: va a su invitación
        if (! $this->hasDoorAccess($request, $invitation)) {
            return redirect()->route('invitation.guest', ['slug' => $slug, 'token' => $token]);
        }

        return view('door.entry', [
            'invitation' => $invitation,
            'guest' => $guest,
            'doorToken' => $invitation->door_token,
            'expected' => GuestPass::expected($guest),
            'remaining' => GuestPass::remaining($guest),
            'stats' => self::stats($invitation),
        ]);
    }

    public function checkIn(CheckInGuestRequest $request, string $slug, string $token): RedirectResponse
    {
        [$invitation, $guest] = $this->findPass($slug, $token);
        abort_unless($this->hasDoorAccess($request, $invitation), 403, 'Este teléfono no está habilitado para la puerta.');

        $people = (int) $request->validated('people');

        // Con candado: dos teléfonos en la misma puerta no pueden hacer pasar al mismo pase dos veces
        $registered = DB::transaction(function () use ($guest, $people): int {
            $fresh = Guest::whereKey($guest->id)->lockForUpdate()->firstOrFail();
            $people = min($people, GuestPass::remaining($fresh));

            if ($people < 1) {
                return 0;
            }

            $fresh->forceFill([
                'checked_in_passes' => $fresh->checked_in_passes + $people,
                'checked_in_at' => $fresh->checked_in_at ?? now(),
            ])->save();

            return $people;
        });

        return redirect()->route('door.entry', ['slug' => $slug, 'token' => $token])
            ->with($registered ? 'door_success' : 'door_error', $registered
                ? ($registered === 1 ? 'Ingreso registrado: 1 persona.' : "Ingreso registrado: {$registered} personas.")
                : 'Este pase ya no tiene personas por ingresar.');
    }

    /** Deshace el último registro de ese pase, por si se tocó por error. */
    public function undo(Request $request, string $slug, string $token): RedirectResponse
    {
        [$invitation, $guest] = $this->findPass($slug, $token);
        abort_unless($this->hasDoorAccess($request, $invitation), 403, 'Este teléfono no está habilitado para la puerta.');

        $guest->forceFill(['checked_in_passes' => 0, 'checked_in_at' => null])->save();

        return redirect()->route('door.entry', ['slug' => $slug, 'token' => $token])
            ->with('door_success', 'Se deshizo el ingreso de este pase.');
    }

    /** Resumen de la puerta: cuántas personas se esperan y cuántas ya entraron. */
    public static function stats(Invitation $invitation): array
    {
        $row = Guest::where('invitation_id', $invitation->id)
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'confirmed' THEN passes_confirmed ELSE 0 END), 0) AS expected")
            ->selectRaw('COALESCE(SUM(checked_in_passes), 0) AS arrived')
            ->selectRaw('COUNT(checked_in_at) AS passes_used')
            ->first();

        return [
            'expected' => (int) $row->expected,
            'arrived' => (int) $row->arrived,
            'passesUsed' => (int) $row->passes_used,
        ];
    }

    public static function cookieName(Invitation $invitation): string
    {
        return 'bida_puerta_'.$invitation->id;
    }

    private function invitationForDoor(string $doorToken): Invitation
    {
        abort_unless(strlen($doorToken) >= 32, 404);

        $invitation = Invitation::where('door_token', $doorToken)->firstOrFail();
        abort_unless(Packages::allowsFor($invitation, 'door'), 404);

        return $invitation;
    }

    /** @return array{0: Invitation, 1: Guest} */
    private function findPass(string $slug, string $token): array
    {
        $invitation = Invitation::where('slug', $slug)->firstOrFail();
        // El control de entrada es del paquete Premium
        abort_unless(Packages::allowsFor($invitation, 'door'), 404);
        $guest = Guest::where('invitation_id', $invitation->id)->where('qr_code_token', $token)->firstOrFail();

        return [$invitation, $guest];
    }

    private function hasDoorAccess(Request $request, Invitation $invitation): bool
    {
        $stored = (string) $request->cookie(self::cookieName($invitation));

        return $invitation->door_token !== null && $stored !== '' && hash_equals($invitation->door_token, $stored);
    }
}
