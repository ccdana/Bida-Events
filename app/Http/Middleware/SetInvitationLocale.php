<?php

namespace App\Http\Middleware;

use App\Models\Invitation;
use App\Support\Localization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deja activo el idioma de la invitación en todo lo que toca un invitado: la página, las respuestas
 * (confirmar, votar, subir una foto) y la puerta. No depende de la dirección ni del navegador: es el
 * que eligió quien la creó (invitations.locale). Las rutas llevan el slug de la invitación o, en la
 * puerta, su código de acceso.
 */
class SetInvitationLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = Invitation::query()
            ->when($request->route('slug'), fn ($query, string $slug) => $query->where('slug', $slug))
            ->when(! $request->route('slug') && $request->route('doorToken'), fn ($query) => $query->where('door_token', $request->route('doorToken')))
            ->value('locale');

        Localization::apply(Localization::resolve($locale));

        $response = $next($request);
        $response->headers->set('Content-Language', Localization::current());

        return $response;
    }
}
