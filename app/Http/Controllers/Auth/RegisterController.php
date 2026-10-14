<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Support\AuthOptions;
use App\Support\ClientCredentials;
use App\Support\Money;
use App\Support\Offers;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Cuenta nueva desde el sitio, con correo y contraseña. Queda como cliente sin plan: entra a su
 * panel enseguida y recibe un correo para confirmar la dirección (la compra lo exige).
 */
class RegisterController extends Controller
{
    public function create(Request $request): View
    {
        abort_unless(AuthOptions::registrationOpen(), 404);

        // Quien eligió un plan en «Únete» llega con él: al crear y confirmar la cuenta, sigue el pago
        $plan = $request->query('plan');

        if (is_string($plan) && isset(Offers::resellerPlans()[$plan])) {
            $request->session()->put('url.intended', route('client.subscription', ['plan' => $plan]));
        }

        return view('auth.register', ['chosenPlan' => $this->chosenPlan($request)]);
    }

    public function store(RegisterRequest $request, ClientCredentials $credentials): RedirectResponse
    {
        abort_unless(AuthOptions::registrationOpen(), 404);

        $user = User::create([
            'name' => $request->validated('name'),
            // El usuario de siempre sigue existiendo (maria.valenzuela), aunque entre con el correo
            'username' => $credentials->username($request->validated('name')),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ]);

        // Envía el correo de verificación (Illuminate\Auth\Listeners\SendEmailVerificationNotification)
        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }

    /** El plan con el que viene quien se registra, para decírselo arriba del formulario (null si no eligió). */
    private function chosenPlan(Request $request): ?array
    {
        $intended = (string) $request->session()->get('url.intended', '');

        if (! str_contains($intended, '/client/suscripcion')) {
            return null;
        }

        parse_str((string) parse_url($intended, PHP_URL_QUERY), $query);
        $plan = is_string($query['plan'] ?? null) ? (Offers::resellerPlans()[$query['plan']] ?? null) : null;

        if ($plan === null) {
            return null;
        }

        return [
            'name' => $plan['name'],
            'price' => Money::format($plan['final_price']),
            'quota' => $plan['quota_per_month'] === null ? 'Invitaciones sin tope' : ($plan['quota_per_month'] === 1 ? '1 invitación al mes' : $plan['quota_per_month'].' invitaciones al mes'),
        ];
    }
}
