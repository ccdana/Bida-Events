<?php

namespace App\Http\Middleware;

use App\Support\LeadSource;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Recuerda 30 días el origen de campaña (utm_* o ?ref=) con el que llegó el visitante,
 * para que el código siga en el mensaje de WhatsApp aunque escriba otro día.
 * Una visita con parámetros nuevos reemplaza al anterior; una sin parámetros no lo toca.
 */
class CaptureLeadSource
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($origin = LeadSource::fromQuery($request)) {
            $response->headers->setCookie(cookie(
                LeadSource::COOKIE,
                json_encode($origin),
                LeadSource::DAYS * 24 * 60,
                httpOnly: true,
                sameSite: 'lax',
            ));
        }

        return $response;
    }
}
