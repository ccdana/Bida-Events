<?php

namespace App\Http\Middleware;

use App\Support\Localization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Idioma de las rutas del sitio (routes/web.php registra cada una por idioma: /precios y /en/pricing).
 * Deja activo el de la ruta y devuelve a su clave en español los parámetros que cambian de dirección
 * (landing, page), para que controladores y vistas trabajen siempre con la misma clave.
 *
 * Uso: ->middleware(SetLocale::class.':en')
 */
class SetLocale
{
    public function handle(Request $request, Closure $next, string $locale): Response
    {
        Localization::apply($locale);

        if (! Localization::isDefault($locale) && $route = $request->route()) {
            foreach (Localization::SLUG_PARAMETERS as $parameter => $group) {
                if (is_string($value = $route->parameter($parameter))) {
                    $route->setParameter($parameter, Localization::canonical($group, $value, $locale));
                }
            }
        }

        $response = $next($request);
        $response->headers->set('Content-Language', Localization::current());

        return $response;
    }
}
