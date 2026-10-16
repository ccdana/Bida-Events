<?php

namespace App\Support;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * En los paneles (admin y cliente) un 403 no muestra la página de error del framework: se vuelve a donde
 * estaba la persona, o a su panel, con un aviso en español. Las llamadas de fetch (JSON) conservan el 403.
 */
class AccessDeniedRedirect
{
    private const GENERIC = 'No tienes acceso a esa sección.';

    public static function handles(Request $request): bool
    {
        return ! $request->expectsJson()
            && ! $request->ajax()
            && $request->is('admin', 'admin/*', 'client', 'client/*', 'dashboard');
    }

    public static function respond(Request $request, ?string $message): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->to(lroute('login'));
        }

        $notice = self::notice($message);
        $panel = $user->isAdmin() ? route('admin.dashboard') : route('client.dashboard');
        $previous = url()->previous($panel);
        $samePage = fn (string $url) => rtrim((string) parse_url($url, PHP_URL_PATH), '/') === rtrim($request->getPathInfo(), '/');
        $sameHost = parse_url($previous, PHP_URL_HOST) === $request->getHost();

        // De vuelta a donde estaba; si no hay a dónde volver (o es la misma página), a su panel
        $target = $sameHost && ! $samePage($previous) ? $previous : $panel;

        if ($samePage($target)) {
            $target = lroute('home');
        }

        return redirect()->to($target, 303)->with('access_notice', $notice);
    }

    /** El mensaje de la regla si está en español; el genérico del framework («This action is unauthorized.») se cambia. */
    private static function notice(?string $message): string
    {
        $message = trim((string) $message);

        return $message === '' || preg_match('/^[\x20-\x7E]+$/', $message) && str_contains($message, 'unauthorized')
            ? self::GENERIC
            : $message;
    }
}
