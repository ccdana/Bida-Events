<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreResellerClientRequest;
use App\Models\Invitation;
use App\Models\User;
use App\Support\ClientCredentials;
use App\Support\ResellerSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * El cliente de un evento del revendedor: un acceso (usuario y contraseña dictables) para que la
 * familia vea sus invitados y descargue sus reportes. Uno por evento y, por mes, no más que las
 * invitaciones del plan; si se creó mal, se elimina y se crea otro (el borrado no cuenta). El revendedor sigue siendo quien edita la invitación.
 */
class ResellerClientController extends Controller
{
    public function store(StoreResellerClientRequest $request, Invitation $invitation, ClientCredentials $credentials): RedirectResponse
    {
        $reseller = $request->user();

        if ($invitation->user_id !== null) {
            return back()->withErrors(['client' => 'Este evento ya tiene su cliente. Elimínalo primero si quieres crear otro.']);
        }

        if (! ResellerSubscription::canCreateClients($reseller)) {
            $limit = ResellerSubscription::quotaLimit($reseller);

            return back()->withErrors(['client' => "Ya creaste los {$limit} accesos de cliente de tu plan este mes: son tantos como las invitaciones que puedes crear."]);
        }

        $name = trim($request->validated('name'));
        $password = $credentials->password();

        $client = DB::transaction(function () use ($invitation, $reseller, $name, $password, $credentials) {
            $client = User::create([
                'name' => $name,
                'username' => $credentials->username($name),
                'password' => Hash::make($password),
                'is_admin' => false,
                'created_by_reseller_id' => $reseller->id,
            ]);

            $invitation->update(['user_id' => $client->id]);

            return $client;
        });

        return back()
            ->with('success', "{$client->name} ya puede entrar a ver sus invitados.")
            // Se muestra una sola vez: la contraseña no se guarda legible
            ->with('client_credentials', ['name' => $client->name, 'username' => $client->username, 'password' => $password]);
    }

    public function destroy(Invitation $invitation): RedirectResponse
    {
        $client = $invitation->user;

        if (! $client) {
            return back();
        }

        // Solo se elimina un cliente que creó este revendedor; los que creó el equipo no se tocan
        abort_unless((int) $client->created_by_reseller_id === (int) auth()->id(), 403, 'Este cliente no lo creaste tú.');

        DB::transaction(function () use ($invitation, $client) {
            $invitation->update(['user_id' => null]);

            // Es un acceso por evento: si no tiene otro evento a su nombre, se borra la cuenta
            if (! Invitation::where('user_id', $client->id)->exists()) {
                $client->delete();
            }
        });

        return back()->with('success', "Se eliminó el acceso de {$client->name}. Ya puedes crear otro.");
    }
}
