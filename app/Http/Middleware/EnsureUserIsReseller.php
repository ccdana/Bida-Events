<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Solo deja pasar a revendedores. Va detrás de «client»: el revendedor también es un cliente,
 * con su panel de siempre, y además puede armar sus propias invitaciones.
 */
class EnsureUserIsReseller
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isReseller()) {
            abort(403, 'Esta sección es para revendedores con suscripción.');
        }

        return $next($request);
    }
}
