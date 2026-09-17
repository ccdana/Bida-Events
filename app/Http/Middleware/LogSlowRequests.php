<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deja en storage/logs/performance-*.log las peticiones que tardan más que
 * config('operations.slow_request_ms'). Se registra la ruta con sus parámetros sin
 * resolver (p/{slug}/i/{token}) para no guardar los enlaces personales de los invitados.
 */
class LogSlowRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = hrtime(true);

        $response = $next($request);

        $milliseconds = (int) round((hrtime(true) - $startedAt) / 1_000_000);

        if ($milliseconds >= config('operations.slow_request_ms', 1500)) {
            Log::channel('performance')->warning('Petición lenta', [
                'ms' => $milliseconds,
                'method' => $request->method(),
                'route' => $request->route()?->getName() ?? $request->route()?->uri() ?? 'sin ruta',
                'status' => $response->getStatusCode(),
            ]);
        }

        return $response;
    }
}
