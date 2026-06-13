<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CachePublicInvitations
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! config('optimizations.http.public_invitation_cache', false)) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');

            return $response;
        }

        if ($request->route('token')) {
            $ttl = (int) config('optimizations.http.guest_invitation_ttl', 300);
            $response->headers->set('Cache-Control', "public, max-age={$ttl}, must-revalidate");
        } else {
            $ttl = (int) config('optimizations.http.public_invitation_ttl', 300);
            $response->headers->set('Cache-Control', "public, max-age={$ttl}, must-revalidate");
        }

        $response->headers->set('Vary', 'Accept-Encoding');

        return $response;
    }
}
