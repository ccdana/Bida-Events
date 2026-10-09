<?php

namespace App\Http\Controllers\Auth\Concerns;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * A dónde va alguien que acaba de entrar (con usuario, correo o Google): a la página que quería
 * abrir o, si no había ninguna, a su panel. Las páginas de entrada no cuentan como destino.
 */
trait RedirectsAfterLogin
{
    protected function redirectAfterLogin(Request $request, User $user): RedirectResponse
    {
        $home = $user->isAdmin() ? route('admin.dashboard') : route('client.dashboard');
        $intended = $request->session()->get('url.intended');

        if (is_string($intended)) {
            $path = parse_url($intended, PHP_URL_PATH) ?: '/';

            if (in_array($path, ['/', '/login', '/client/login', '/dashboard', '/registro'], true)) {
                $request->session()->forget('url.intended');
                $intended = null;
            }
        }

        return $intended ? redirect()->intended($home) : redirect()->to($home);
    }
}
