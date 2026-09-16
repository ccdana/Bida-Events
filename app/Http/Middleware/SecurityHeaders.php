<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabeceras de seguridad para todas las respuestas HTML.
 *
 * La política de contenido (CSP) sale en modo "solo reporte" hasta que se active
 * CSP_ENFORCE=true: así se puede medir qué bloquearía antes de exigirla.
 * Las invitaciones cargan Cloudinary, Google Fonts, Google Maps y YouTube, y Alpine
 * evalúa expresiones, por eso la política los contempla.
 */
class SecurityHeaders
{
    private const POLICY = [
        "default-src 'self'",
        "base-uri 'self'",
        "form-action 'self'",
        "frame-ancestors 'self'",
        "object-src 'none'",
        "img-src 'self' data: blob: https://res.cloudinary.com https://picsum.photos https://*.googleusercontent.com https://*.ytimg.com",
        "media-src 'self' blob: https://res.cloudinary.com",
        "font-src 'self' data: https://fonts.gstatic.com",
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
        "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
        "connect-src 'self' https://res.cloudinary.com https://api.cloudinary.com",
        "frame-src 'self' https://www.google.com https://maps.google.com https://www.youtube.com https://www.youtube-nocookie.com",
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Permissions-Policy', 'camera=(self), microphone=(), geolocation=(), payment=(), interest-cohort=()');

        if (config('security.csp.enabled', true)) {
            $header = config('security.csp.enforce', false)
                ? 'Content-Security-Policy'
                : 'Content-Security-Policy-Report-Only';

            $response->headers->set($header, implode('; ', self::POLICY));
        }

        return $response;
    }
}
